<?php

namespace App\Models;

use App\Observers\NoticeObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use SoftDeletes, NoticeObserver;

    protected $fillable = [
        'notice_type',
        'title',
        'notice',
        'published_at',
        'dismissable',
    ];

    public static $notice_types = [
        'notice', 'success', 'info', 'warning', 'error'
    ];

    public static $user_scope = [
        'user',
        'role',
    ];

    protected $casts = [
        'dismissable' => 'boolean',
    ];

    public function noticeables()
    {
        return $this->hasMany(Noticeable::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'noticeables', 'notice_id', 'user_id');
    }

}
