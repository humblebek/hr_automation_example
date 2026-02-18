<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $guarded = [];

    public function occupations(): HasMany
    {
        return $this->hasMany(Occupation::class);
    }

    public function departmentTranslations(): HasMany
    {
        return $this->hasMany(DepartmentTranslation::class);
    }
}
