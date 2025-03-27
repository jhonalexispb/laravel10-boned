<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait AuditableTrait
{
    public static function bootAuditableTrait()
    {
        static::creating(function (Model $model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function (Model $model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function creador()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function actualizador()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}