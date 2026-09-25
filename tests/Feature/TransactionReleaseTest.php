<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Jobs\ProcessPayout;
use App\Jobs\ProcessPayoutReconciliation;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Fedapay\FedapayService;
use App\Services\Fedapay\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class TransactionReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_links_the_transaction_to_the_task(): void
    {
        Mail::fake();

        $client = User::factory()->client()->create();
        $prestataire = User::factory()->prestataire()->create();
        $task = Task::factory()->pending()->create(['client_id' => $client->id]);

        Auth::login($client);

        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('verifyCollect')
            ->once()
            ->with('COLLECT_123')
            ->andReturn([
                'success' => true,
                'data' => (object) [
                    'id' => 'COLLECT_123',
                    'amount' => 1000,
                    'mode' => 'mobile_money',
                    'description' => 'Test payment',
                    'reference' => (object) ['task_id' => $task->id],
                    'custom_metadata' => (object) ['prestataire_id' => $prestataire->id],
                ],
            ]);

        $result = (new TransactionService($fedapay))->handleTransaction('COLLECT_123', $task->id);

        $transaction = Transaction::query()->where('fedapay_transaction_id', 'COLLECT_123')->firstOrFail();

        $this->assertTrue($result['success']);
        $this->assertSame($transaction->id, $task->fresh()->transaction_id);
        $this->assertSame(TaskStatus::PENDING, $task->fresh()->status);
        $this->assertSame(TransactionStatus::ESCROW_LOCK, $transaction->fresh()->status);
    }

    public function test_release_keeps_task_pending_until_payout_confirms(): void
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['phone' => '+22900000000']);

        $task = Task::factory()->create([
            'client_id' => $client->id,
            'status' => TaskStatus::DELIVERED,
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
            'description' => 'Test payout',
            'status' => TransactionStatus::ESCROW_LOCK,
        ]);

        Auth::login($client);
        Queue::fake();

        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('payout')->once()->andReturn([
            'success' => true,
            'data' => (object) ['id' => 'SIMULATED_PAYOUT_123'],
        ]);

        $service = new TransactionService($fedapay);

        $result = $service->release($transaction->id);

        $this->assertTrue($result['success']);
        Queue::assertPushed(ProcessPayout::class);
        $this->assertSame(TransactionStatus::RELEASING, $transaction->fresh()->status);
        $this->assertSame(TaskStatus::DELIVERED, $task->fresh()->status);
    }

    public function test_reconciliation_marks_releasing_without_payout_id_failed(): void
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['phone' => '+22900000000']);
        $task = Task::factory()->create([
            'client_id' => $client->id,
            'status' => TaskStatus::DELIVERED,
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
            'status' => TransactionStatus::RELEASING,
        ]);
        $task->transaction_id = $transaction->id;
        $task->save();
        $transaction->forceFill(['updated_at' => now()->subMinutes(20)])->save();

        (new ProcessPayoutReconciliation)->handle(Mockery::mock(FedapayService::class));

        $this->assertSame(TransactionStatus::FAILED, $transaction->fresh()->status);
        $this->assertSame(TaskStatus::DELIVERED, $task->fresh()->status);
    }

    public function test_payout_job_updates_transaction_and_task_together(): void
    {
        Queue::fake();

        $client = User::factory()->create();
        $prestataire = User::factory()->create(['phone' => '+22900000000']);
        $task = Task::factory()->create([
            'client_id' => $client->id,
            'status' => TaskStatus::DELIVERED,
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
            'status' => TransactionStatus::RELEASING,
            'fedapay_payout_id' => 'PAYOUT_123',
        ]);
        $task->transaction_id = $transaction->id;
        $task->save();

        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('sendPayout')
            ->once()
            ->with('PAYOUT_123')
            ->andReturn(['success' => true]);

        (new ProcessPayout($transaction->id))->handle($fedapay);

        $this->assertSame(TransactionStatus::RELEASED, $transaction->fresh()->status);
        $this->assertSame(TaskStatus::VALIDATED, $task->fresh()->status);
    }

    public function test_reconciliation_updates_transaction_and_task_together(): void
    {
        Queue::fake();

        $client = User::factory()->create();
        $prestataire = User::factory()->create(['phone' => '+22900000000']);
        $task = Task::factory()->create([
            'client_id' => $client->id,
            'status' => TaskStatus::DELIVERED,
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
            'status' => TransactionStatus::RELEASING,
            'fedapay_payout_id' => 'PAYOUT_RECON_123',
        ]);
        $task->transaction_id = $transaction->id;
        $task->save();
        $transaction->forceFill(['updated_at' => now()->subMinutes(20)])->save();

        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('getPayoutStatus')
            ->once()
            ->with('PAYOUT_RECON_123')
            ->andReturn(['success' => true, 'status' => 'sent']);

        (new ProcessPayoutReconciliation)->handle($fedapay);

        $this->assertSame(TransactionStatus::RELEASED, $transaction->fresh()->status);
        $this->assertSame(TaskStatus::VALIDATED, $task->fresh()->status);
    }
}
