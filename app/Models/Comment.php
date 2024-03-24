<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'comment'
    ];  

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function($model) {
            $model->created_by = 1; //auth()->user()->id;
        });

        static::updating(function($model) {
            $model->updated_by = 1; //auth()->user()->id;
        });

        static::deleting(function($model) {
            $model->deleted_by = 1; //auth()->user()->id;
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
