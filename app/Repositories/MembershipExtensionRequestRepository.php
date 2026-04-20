<?php

namespace App\Repositories;

use App\Models\Membership;
use App\Models\MembershipExtensionRequest;
use Illuminate\Support\Facades\DB;

class MembershipExtensionRequestRepository extends BaseRepository
{
    public function __construct(
        protected MembershipRepository $membershipRepository
    ) {
    }

    /**
     * Store a new extension request.
     *
     * @param array $attributes
     * @return mixed
     */
    public function store(array $attributes): mixed
    {
        return DB::transaction(function () use ($attributes) {
            $validated = [
                'membership_id' => $attributes['membership_id'] ?? null,
                'requested_by' => $attributes['requested_by'] ?? null,
                'reason' => $attributes['reason'] ?? null,
                'requested_days' => $attributes['requested_days'] ?? null,
            ];

            if (! isset($validated['membership_id'], $validated['reason'], $validated['requested_days'])) {
                throw new \Exception(__('Missing required attributes.'));
            }

            $membership = Membership::find($validated['membership_id']);
            if (! $membership) {
                throw new \Exception(__('Membership not found.'));
            }

            if ($membership->status !== 'active') {
                throw new \Exception(__('Extension requests require an active membership.'));
            }

            if (
                $membership->extensionRequests()
                    ->where('status', 'pending')
                    ->exists()
            ) {
                throw new \Exception(__('This membership already has a pending extension request.'));
            }

            return MembershipExtensionRequest::create([
                'membership_id' => $validated['membership_id'],
                'requested_by' => $validated['requested_by'],
                'reason' => $validated['reason'],
                'requested_days' => $validated['requested_days'],
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Approve a pending request and extend the membership in one transaction.
     *
     * @param mixed $model
     * @param array $attributes
     * @return mixed
     */
    public function update(mixed $model, array $attributes): mixed
    {
        return false;
    }

    /**
     * Approve an extension request.
     *
     * @throws \Throwable
     */
    public function approve(MembershipExtensionRequest $extensionRequest, int $approverUserId): void
    {
        DB::transaction(function () use ($extensionRequest, $approverUserId) {
            if ($extensionRequest->status !== 'pending') {
                throw new \Exception(__('Only pending requests can be approved.'));
            }

            $membership = $extensionRequest->membership;
            $this->membershipRepository->extendActiveMembership($membership, (int) $extensionRequest->requested_days);

            $extensionRequest->status = 'approved';
            $extensionRequest->approved_by = $approverUserId;
            $extensionRequest->save();
        });
    }

    /**
     * Reject an extension request.
     */
    public function reject(MembershipExtensionRequest $extensionRequest, int $actorUserId, ?string $rejectionReason): void
    {
        DB::transaction(function () use ($extensionRequest, $actorUserId, $rejectionReason) {
            if ($extensionRequest->status !== 'pending') {
                throw new \Exception(__('Only pending requests can be rejected.'));
            }

            $extensionRequest->status = 'rejected';
            $extensionRequest->approved_by = $actorUserId;
            $extensionRequest->rejection_reason = $rejectionReason;
            $extensionRequest->save();
        });
    }
}
