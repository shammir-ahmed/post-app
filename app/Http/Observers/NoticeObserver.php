<?php

namespace App\Observers;

trait NoticeObserver
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notice) {
            $notice->updated_by = auth()->user()->id;
            $notice->created_by = auth()->user()->id;
        });

        static::updating(function ($notice) {
            $notice->updated_by = auth()->user()->id;
        });
    }
}
