<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SnapCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_nsfw',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_nsfw' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(SnapProfile::class, 'snap_category_snap_profile');
    }
}
