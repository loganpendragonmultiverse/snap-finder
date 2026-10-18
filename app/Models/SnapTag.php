<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SnapTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_nsfw',
    ];

    protected function casts(): array
    {
        return [
            'is_nsfw' => 'boolean',
        ];
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(SnapProfile::class, 'snap_profile_snap_tag');
    }
}
