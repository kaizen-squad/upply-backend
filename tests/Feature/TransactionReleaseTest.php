<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Fedapay\FedapayService;
use App\Services\Fedapay\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class TransactionReleaseTest extends TestCase
{
    use RefreshDatabase;

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

        $fedapay = Mockery::mock(FedapayService::class);
        $fedapay->shouldReceive('payout')->once()->andReturn([
            'success' => true,
            'data' => (object) ['id' => 'SIMULATED_PAYOUT_123'],
        ]);

        $service = new TransactionService($fedapay);

        $result = $service->release($transaction->id);

        $this->assertTrue($result['success']);
        $this->assertSame(TransactionStatus::RELEASING, $transaction->fresh()->status);
        $this->assertSame(TaskStatus::DELIVERED, $task->fresh()->status);
    }
}
