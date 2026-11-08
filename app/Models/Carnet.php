<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Carnet extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'carnet_lot_id',  // Lié au lot de création/achat
        'collector_id',   // Lié au collecteur qui détient le carnet
        'number',
        'status',         // in_stock, assigned_to_collector, sold, expired
    ];

    /**
     * Relationship: Client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relationship: Lot de carnets.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(CarnetLot::class, 'carnet_lot_id');
    }

    /**
     * Relationship: Collecteur (User).
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
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
