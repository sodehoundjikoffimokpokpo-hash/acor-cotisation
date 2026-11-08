<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Importe les classes nécessaires pour le journal d'activité
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\LocationScopable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory, LogsActivity, LocationScopable; // Ajoute ici le trait LogsActivity

    protected $fillable = [
        'name',
        'price',
        'stock_quantity',
        'country_id',
        'region_id',
        'zone_id',
        'purchase_price',
        'transport_fees',
        'search_fees',
        'collector_commission',
        'admin_fees',
        'price_provision',
        'acor_margin',
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
     * Configuration du journal d'activité pour ce modèle
     */

    /**
     * Un article peut être assigné à plusieurs clients.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }





    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
