<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapProfileReport extends Model
{
    protected $fillable = [
        'snap_profile_id',
        'reporter_user_id',
        'reason',
        'details',
        'ip_address',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(SnapProfile::class, 'snap_profile_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }
}
