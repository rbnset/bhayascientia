<?php

namespace App\Support;

class ReviewDecisionMapper
{
    public static function publicationStatusFromDecision(?string $decision): ?string
    {
        return match ($decision) {
            'revision_required' => 'revision_required',
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            default => null,
        };
    }
}
