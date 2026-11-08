<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionValidated
{
    use Dispatchable, SerializesModels;

    /**
     * The validated contribution instance.
     *
     * @var \App\Models\Contribution
     */
    public $contribution;

    /**
     * Create a new event instance.
     */
    public function __construct(Contribution $contribution)
    {
        $this->contribution = $contribution;
    }
}
