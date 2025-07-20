<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadCapture extends Model
{
    protected $fillable = [
        'email',
        'user_id', // Make sure this is fillable if you set it in other contexts
        'context',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];


    // If you ever do add authentication, you might link it to a user:
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
