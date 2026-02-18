<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $guarded = [];
    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class);
    }

    public function questionTranslations(): HasMany
    {
        return $this->hasMany(QuestionTranslation::class);
    }
}
