<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use App\Traits\Auditable;
use App\Traits\LocationScopable;

class Client extends Model
{
    use Auditable;
    use HasFactory, LocationScopable;

    protected $appends = ['other_active_plans'];

    protected $fillable = [
        'name',
        'firstname',
        'phone',
        'address',
        'daily_contribution',
        'target_amount',
        'desired_article',
        'status', // active, goal_reached, completed
        'collector_id',
        'article_id',
        'delivered_at',
        'country_id',
        'region_id',
        'zone_id',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Relationship: Creator Collector.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    /**
     * Relationship: Article.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Relationship: Carnets.
     */
    public function carnets(): HasMany
    {
        return $this->hasMany(Carnet::class);
    }

    /**
     * Relationship: Active Carnet.
     */
    public function activeCarnet(): HasOne
    {
        return $this->hasOne(Carnet::class)->where('status', 'active');
    }

    /**
     * Get other active or goal_reached plans/subscriptions of the same client (using phone).
     */
    public function otherPlans()
    {
        if (empty($this->phone)) {
            return collect();
        }
        return self::where('phone', $this->phone)
            ->where('id', '!=', $this->id)
            ->whereIn('status', ['active', 'goal_reached'])
            ->get();
    }

    public function getOtherActivePlansAttribute()
    {
        return $this->otherPlans()->map(function($plan) {
            return [
                'id' => $plan->id,
                'desired_article' => $plan->desired_article,
                'daily_contribution' => $plan->daily_contribution,
                'target_amount' => $plan->target_amount,
                'status' => $plan->status,
                'carnet_number' => $plan->activeCarnet->number ?? 'Aucun',
                'total_paid' => $plan->totalPaid(),
            ];
        })->toArray();
    }

    /**
     * Relationship: Contributions.
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Calculate total paid amount (all contributions).
     */
    public function totalPaid(): float
    {
        return (float) $this->contributions()->sum('amount');
    }

    /**
     * Calculate total validated paid amount.
     */
    public function totalValidatedPaid(): float
    {
        return (float) $this->contributions()->where('validation_status', 'validated')->sum('amount');
    }

    /**
     * Check if goal is reached and update status.
     */
    public function checkGoalStatus(): void
    {
        $totalCotise = $this->totalValidatedPaid();
        $objectif = $this->article ? $this->article->price : $this->target_amount;

        if ($objectif > 0 && $totalCotise >= $objectif && $this->status === 'active') {
            $this->update(['status' => 'goal_reached']);

            // Log event
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'GOAL_REACHED',
                'description' => "Le client {$this->firstname} {$this->name} a atteint son objectif ({$totalCotise} FCFA).",
                'ip_address' => request()->ip(),
            ]);
        }
    }

    /**
     * Calculate number of days covered.
     */
    public function daysCovered(): int
    {
        if ($this->daily_contribution <= 0) {
            return 0;
        }
        return (int) floor($this->totalPaid() / $this->daily_contribution);
    }

    public function cardCost(): float
    {
        return (float) ($this->activeCarnet->lot->card_cost ?? 1000.00);
    }

    public function daysCoveredTarget(): int
    {
        return (int) ($this->activeCarnet->lot->days_covered ?? 100);
    }

    public function cardCostDaily(): float
    {
        $days = $this->daysCoveredTarget();
        return $days > 0 ? $this->cardCost() / $days : 0;
    }

    public function totalTargetAmount(): float
    {
        return (float) ($this->article ? $this->article->price : $this->target_amount);
    }

    public function margeRealisee(): float
    {
        return $this->totalTargetAmount() - $this->cardCost();
    }

    public function soldeRestant(): float
    {
        return max(0, $this->totalTargetAmount() - $this->totalValidatedPaid());
    }

    /**
     * Calculate precise contribution time.
     */
    public function contributionTimeFormatted(): string
    {
        $totalDays = $this->daysCovered();
        $months = (int) floor($totalDays / 30);
        $days = $totalDays % 30;

        $parts = [];
        if ($months > 0) {
            $parts[] = $months . ' mois';
        }
        if ($days > 0 || empty($parts)) {
            $parts[] = $days . ' jour' . ($days > 1 ? 's' : '');
        }

        return implode(' et ', $parts);
    }
}
