<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Contribution extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'collector_id',
        'amount',
        'payment_date',
        'validation_status', // pending, validated
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Relationship: Client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relationship: Collector.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    /**
     * Relationship: Validator (Admin).
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
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
