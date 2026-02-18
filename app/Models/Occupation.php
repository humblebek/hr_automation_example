<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Occupation extends Model
{

    protected $guarded = [];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function occupationTranslations(): HasMany
    {
        return $this->hasMany(OccupationTranslation::class);
    }
}
