<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SnapProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'snap_username',
        'headline',
        'category',
        'bio',
        'looking_for',
        'location',
        'zip_code',
        'profile_image',
        'is_adult',
        'is_approved',
        'is_featured',
        'status',
        'status_reason',
        'paused_at',
    ];

    protected function casts(): array
    {
        return [
            'is_adult' => 'boolean',
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
            'paused_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(SnapCategory::class, 'snap_category_snap_profile');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(SnapTag::class, 'snap_profile_snap_tag');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SnapProfileReport::class);
    }

    public function isPubliclyApproved(): bool
    {
        return $this->status === 'approved' && ! $this->paused_at;
    }

    public function isNsfw(): bool
    {
        return $this->is_adult || $this->categories->contains('is_nsfw', true) || $this->tags->contains('is_nsfw', true);
    }
}
