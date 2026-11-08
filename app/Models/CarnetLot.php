<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\LocationScopable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarnetLot extends Model
{
    use Auditable, LocationScopable;
    protected $fillable = [
        'lot_number', 'batch_number', 'initial_quantity', 'remaining_quantity',
        'purchase_date', 'expiration_date', 'penalty_amount', 'penalty_applied',
        'country_id', 'region_id', 'zone_id',
        'card_cost', 'days_covered', 'daily_rate',
        'article_name', 'purchase_price', 'transport_fees', 'search_fees',
        'collector_commission', 'admin_fees', 'price_provision', 'acor_margin', 'final_price'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiration_date' => 'date',
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

    public function carnets()
    {
        return $this->hasMany(Carnet::class);
    }

    // Vérifie si le lot a expiré
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expiration_date);
    }

    use LogsActivity;



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
