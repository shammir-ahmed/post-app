<?php

namespace App\Observers;

use App\Models\User;
use App\Models\NoticeVisible;
use Illuminate\Support\Facades\Redis;

trait UserObserver
{
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            $payloadPacket = [
                'user_id' => $model->id,
                'first_name' => $model->first_name,
                'last_name' => $model->last_name,
            ];
            if (($model->isDirty('first_name') || $model->isDirty('last_name')) && $model->hasRole('broker')) {
                // Redis::publish(
                //     config('app.channel_prefix').'.name.update',
                //     json_encode([
                //         'user' => $payloadPacket
                //     ])
                // );
            }
        });
    }
}
