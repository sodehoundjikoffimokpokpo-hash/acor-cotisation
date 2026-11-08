<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class CaisseClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'collector_id',
        'closure_date',
        'carnets_vendus',
        'nb_cotisations',
        'montant_total',
        'status',
        'validated_by',
        'validated_at'
    ];

    /**
     * Relation avec le collecteur (User)
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    /**
     * Relation avec l'administrateur qui valide (User)
     */
    public function validator()
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
