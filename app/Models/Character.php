<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'short_description',
        'personality',
        'scenario',
        'first_message',
        'long_bio',
        'writing_style',
        'tone',
        'tags',
        'lore_notes',
        'default_model',
        'temperature',
        'max_tokens',
        'top_p',
        'is_active',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'top_p' => 'float',
            'max_tokens' => 'integer',
            'is_active' => 'boolean',
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class)->latest('updated_at');
    }

    public function memoryEntries(): HasMany
    {
        return $this->hasMany(MemoryEntry::class)->latest('is_pinned')->latest('updated_at');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CharacterVersion::class)->latest('id');
    }

    public function snapshotPayload(): array
    {
        return $this->only([
            'name',
            'short_description',
            'personality',
            'scenario',
            'first_message',
            'long_bio',
            'writing_style',
            'tone',
            'tags',
            'lore_notes',
            'default_model',
            'temperature',
            'max_tokens',
            'top_p',
            'is_active',
            'is_favorite',
        ]);
    }

    public function duplicateForUser(int $userId): self
    {
        return static::create([
            ...$this->only([
                'short_description',
                'personality',
                'scenario',
                'first_message',
                'long_bio',
                'writing_style',
                'tone',
                'tags',
                'lore_notes',
                'default_model',
                'temperature',
                'max_tokens',
                'top_p',
                'is_active',
                'is_favorite',
            ]),
            'user_id' => $userId,
            'name' => $this->name.' Copy',
            'slug' => $this->slug.'-copy-'.strtolower(str()->random(4)),
        ]);
    }
}
