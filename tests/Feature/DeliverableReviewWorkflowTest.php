<?php

namespace Tests\Feature;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\DTOs\Review\ReviewStoreDTO;
use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\DomainException;
use App\Http\Requests\Deliverable\SubmitDeliverableRequest;
use App\Models\Application;
use App\Models\Deliverable;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DeliverableService;
use App\Services\Fedapay\TransactionService;
use App\Services\ReviewService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class DeliverableReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_without_transaction_returns_a_domain_error(): void
    {
        $client = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create();
        $task = Task::factory()->pending()->create(['client_id' => $client->id]);

        Application::create([
            'task_id' => $task->id,
            'prestataire_id' => $prestataire->id,
            'message' => 'Test application',
            'status' => ApplicationStatus::ACCEPTED,
        ]);

        Auth::login($prestataire);

        $service = new DeliverableService(Mockery::mock(TransactionService::class));
        $request = SubmitDeliverableRequest::create('/api/deliverables/submit', 'POST', [
            'content' => 'Test deliverable',
            'task_id' => $task->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Payment must be confirmed before submitting a deliverable.');

        $service->submit(
            $prestataire,
            new SubmitDeliverableDTO('Test deliverable', $task->id),
            $request
        );
    }

    public function test_review_validation_returns_422_with_field_errors(): void
    {
        $client = User::factory()->client()->create();
        $task = Task::factory()->validated()->create(['client_id' => $client->id]);
        $token = $client->createToken('test', ['server:access'], now()->addHour())->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/tasks/{$task->id}/review", ['rating' => 6]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['data' => ['rating']]);
    }

    public function test_review_creation_updates_the_reviewee_rating(): void
    {
        $client = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create([
            'rating_avg' => 3,
            'rating_count' => 0,
        ]);
        $task = Task::factory()->validated()->create(['client_id' => $client->id]);

        Application::create([
            'task_id' => $task->id,
            'prestataire_id' => $prestataire->id,
            'message' => 'Test application',
            'status' => ApplicationStatus::ACCEPTED,
        ]);

        Auth::login($client);

        (new ReviewService)->create(
            $client,
            new ReviewStoreDTO(5, null),
            $task
        );

        $this->assertDatabaseHas('reviews', [
            'task_id' => $task->id,
            'reviewer_id' => $client->id,
            'reviewee_id' => $prestataire->id,
            'rating' => 5,
        ]);
        $this->assertSame(1, $prestataire->fresh()->rating_count);
        $this->assertEquals(5, $prestataire->fresh()->rating_avg);
    }

    public function test_review_service_rejects_out_of_range_rating(): void
    {
        $client = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create();
        $task = Task::factory()->validated()->create(['client_id' => $client->id]);

        Application::create([
            'task_id' => $task->id,
            'prestataire_id' => $prestataire->id,
            'message' => 'Test application',
            'status' => ApplicationStatus::ACCEPTED,
        ]);

        Auth::login($client);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Rating must be between 1 and 5.');

        (new ReviewService)->create(
            $client,
            new ReviewStoreDTO(6, null),
            $task
        );
    }

    public function test_review_reading_is_restricted_to_task_participants(): void
    {
        $client = User::factory()->client()->create();
        $unrelatedClient = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create();
        $task = Task::factory()->validated()->create(['client_id' => $client->id]);

        Application::create([
            'task_id' => $task->id,
            'prestataire_id' => $prestataire->id,
            'message' => 'Test application',
            'status' => ApplicationStatus::ACCEPTED,
        ]);

        Auth::login($unrelatedClient);

        $this->expectException(AuthorizationException::class);

        (new ReviewService)->getForTask($task);
    }

    public function test_deliverable_validation_waits_for_payout_confirmation(): void
    {
        $client = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create();
        $task = Task::factory()->create([
            'client_id' => $client->id,
            'status' => TaskStatus::DELIVERED,
        ]);
        $deliverable = Deliverable::create([
            'prestataire_id' => $prestataire->id,
            'task_id' => $task->id,
            'content' => 'Test deliverable',
            'submitted_at' => now(),
        ]);
        Transaction::create([
            'task_id' => $task->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount_gross' => 1000,
            'commission' => 100,
            'amount_net' => 900,
            'currency' => 'XOF',
            'payment_method' => 'mobile_money',
            'status' => TransactionStatus::FAILED,
        ]);
        $transaction = Transaction::create([
            'task_id' => $task->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount_gross' => 1000,
            'commission' => 100,
            'amount_net' => 900,
            'currency' => 'XOF',
            'payment_method' => 'mobile_money',
            'status' => TransactionStatus::ESCROW_LOCK,
        ]);
        $task->transaction_id = $transaction->id;
        $task->save();

        $transactionService = Mockery::mock(TransactionService::class);
        $transactionService->shouldReceive('release')
            ->once()
            ->with($transaction->id)
            ->andReturn(['success' => true]);

        Auth::login($client);

        (new DeliverableService($transactionService))->validate($deliverable);

        $this->assertSame(TaskStatus::DELIVERED, $task->fresh()->status);
    }

    public function test_deliverable_validation_keeps_task_delivered_when_release_fails(): void
    {
        $client = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create();
        $task = Task::factory()->create([
            'client_id' => $client->id,
            'status' => TaskStatus::DELIVERED,
        ]);
        $deliverable = Deliverable::create([
            'prestataire_id' => $prestataire->id,
            'task_id' => $task->id,
            'content' => 'Test deliverable',
            'submitted_at' => now(),
        ]);
        $transaction = Transaction::create([
            'task_id' => $task->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount_gross' => 1000,
            'commission' => 100,
            'amount_net' => 900,
            'currency' => 'XOF',
            'payment_method' => 'mobile_money',
            'status' => TransactionStatus::ESCROW_LOCK,
        ]);
        $task->transaction_id = $transaction->id;
        $task->save();

        $transactionService = Mockery::mock(TransactionService::class);
        $transactionService->shouldReceive('release')
            ->once()
            ->with($transaction->id)
            ->andReturn(['success' => false, 'error' => 'Payout failed']);

        Auth::login($client);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Payout failed');

        try {
            (new DeliverableService($transactionService))->validate($deliverable);
        } finally {
            $this->assertSame(TaskStatus::DELIVERED, $task->fresh()->status);
        }
    }
}
