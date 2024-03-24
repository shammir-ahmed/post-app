<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticeable extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'user_id',
        'read_at',
        'closed_at'
    ];

    const UNREAD = 0;
    const READ = 1;
    const DISMISS = 2;

    public $visibility = [
        'unread', 'read', 'dismiss'
    ];

    public function notice()
    {
        $this->belongsTo(Notice::class);
    }

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
