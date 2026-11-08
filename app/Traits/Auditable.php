<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        // Enregistre automatiquement lors d'une mise à jour (UPDATE)
        static::updated(function ($model) {
            $old = $model->getOriginal();
            $new = $model->getAttributes();
            $changed = array_diff_assoc($new, $old);

            if (!empty($changed)) {
                AuditLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'UPDATE',
                    'description' => 'Mise à jour de ' . class_basename($model) . ' (ID: ' . $model->id . ')',
                    'model_type'  => get_class($model),
                    'model_id'    => $model->id,
                    'old_values'  => array_intersect_key($old, $changed), // Plus besoin de json_encode grâce aux $casts
                    'new_values'  => $changed,
                    'ip_address'  => request()->ip(),
                ]);
            }
        });

        // Enregistre automatiquement lors d'une création (CREATE)
        static::created(function ($model) {
            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'CREATE',
                'description' => 'Création de ' . class_basename($model) . ' (ID: ' . $model->id . ')',
                'model_type'  => get_class($model),
                'model_id'    => $model->id,
                'new_values'  => $model->getAttributes(),
                'ip_address'  => request()->ip(),
            ]);
        });
    }
}
