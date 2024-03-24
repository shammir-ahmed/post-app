<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'status','description'
    ];

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function($model) {
            $model->slug = Str::slug($model->title).'-'.Str::random(10);
            $model->created_by = 1; //auth()->user()->id;
        });

        static::updating(function($model) {
            $model->updated_by = 1; //auth()->user()->id;
        });

        static::deleting(function($model) {
            $model->deleted_by = 1; //auth()->user()->id;
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
