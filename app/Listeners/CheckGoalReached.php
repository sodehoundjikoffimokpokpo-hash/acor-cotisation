<?php

namespace App\Listeners;

use App\Events\ContributionValidated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckGoalReached
{
    /**
     * Handle the event.
     */
    public function handle(ContributionValidated $event): void
    {
        $contribution = $event->contribution;
        if ($contribution && $contribution->client) {
            $contribution->client->checkGoalStatus();
        }
    }
}
