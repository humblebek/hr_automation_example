<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $guarded = [];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'status');
    }

    public function statusTranslations(): HasMany
    {
        return $this->hasMany(StatusTranslation::class);
    }
}
