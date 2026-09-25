<?php

namespace App\Services;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\DomainException;
use App\Http\Requests\Deliverable\SubmitDeliverableRequest;
use App\Http\Resources\DeliverableResource;
use App\Http\Resources\TaskResource;
use App\Models\Deliverable;
use App\Models\Task;
use App\Models\User;
use App\Services\Fedapay\TransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeliverableService
{
    public function __construct(
        public TransactionService $transactionService
    ) {}

    public function submit(User $prestataire, SubmitDeliverableDTO $data, SubmitDeliverableRequest $request)
    {
        return DB::transaction(function () use ($prestataire, $data, $request) {
            $task = Task::with('transaction')->lockForUpdate()->findOrFail($data->task_id);

            Gate::authorize('submit', [Deliverable::class, $task]);

            if ($task->status !== TaskStatus::PENDING) {
                throw new DomainException('This task is not waiting for deliverable.');
            }

            if ($task->transaction === null) {
                throw new DomainException('Payment must be confirmed before submitting a deliverable.');
            }

            if ($task->transaction->status !== TransactionStatus::ESCROW_LOCK) {
                throw new DomainException('Payment is not secured in escrow.');
            }

            $deliverable_data = [
                'prestataire_id' => $prestataire->id,
                'task_id' => $task->id,
                'content' => $data->content,
                'submitted_at' => now(),
            ];

            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $deliverable_data['file_path'] = $file->store('uploads/deliverables', 'public');
                $deliverable_data['file_size'] = $file->getSize();
                $deliverable_data['file_name'] = $file->getBasename();
                $deliverable_data['file_type'] = $file->getMimeType();
            }

            $newDeliverable = Deliverable::create($deliverable_data);

            $task->update(['status' => TaskStatus::DELIVERED]);

            return new DeliverableResource($newDeliverable);
        });
    }

    public function get(Task $task)
    {
        Gate::authorize('get', [Deliverable::class, $task]);

        if ($task->status !== TaskStatus::DELIVERED) {
            throw new DomainException('This task has not yet received any deliverables.');
        }

        $deliverable = $task->deliverable;

        return new DeliverableResource($deliverable->load(['task', 'prestataire']));
    }

    public function validate(Deliverable $deliverable)
    {
        Gate::authorize('validate', [Deliverable::class, $deliverable]);

        $task = Task::with('transaction')->findOrFail($deliverable->task_id);

        if ($task->status !== TaskStatus::DELIVERED) {
            throw new DomainException("This task isn't delivered yet.");
        }

        if ($task->transaction === null) {
            throw new DomainException('Payment must be confirmed before validating a deliverable.');
        }

        $release = $this->transactionService->release($task->transaction->id);

        if (($release['success'] ?? false) !== true) {
            throw new DomainException($release['error'] ?? $release['message'] ?? 'Payment release failed.');
        }

        return new TaskResource($task->fresh());
    }
}
