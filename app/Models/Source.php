<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $guarded = [];

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\Application::class, 'source_id');
    }

}
