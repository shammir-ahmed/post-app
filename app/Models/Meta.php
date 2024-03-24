<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Meta extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value'
    ];

    protected $hidden = [
        'is_hidden'
    ];

    protected static function booted()
    {
        static::addGlobalScope('visible', function (Builder $builder) {
            $builder->where('is_hidden', false);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
