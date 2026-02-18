<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $guarded = [];

    public function occupations(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(\App\Models\District::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Source::class, 'source_id');
    }

    public function occupationStatuses(): HasMany
    {
        return $this->hasMany(ApplicationOccupation::class);
    }

}
