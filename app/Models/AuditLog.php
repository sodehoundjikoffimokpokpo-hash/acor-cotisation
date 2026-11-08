<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use App\Traits\Auditable; // <--- SUPPRIME CETTE LIGNE
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class AuditLog extends Model
{
    use HasFactory;
    // use Auditable; // <--- SUPPRIME CETTE LIGNE AUSSI

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Dans app/Models/AuditLog.php

public static function logAction($action, $description, $old = null, $new = null, $model = null)
{
    self::create([
        'user_id'     => auth()->id(),
        'action'      => $action, // Ex: 'VALIDATION', 'ANNULATION'
        'description' => $description,
        'model_type'  => $model ? get_class($model) : null,
        'model_id'    => $model ? $model->id : null,
        'old_values'  => $old,
        'new_values'  => $new,
        'ip_address'  => request()->ip(),
    ]);
}


use LogsActivity;



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

// Dans AuditLog.php

}
