<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationLocation extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'address',
        'longitude',
        'latitude',
        'tel',
        'fax',
        'city',
        'state',
        'zip',
        'opening',
        'working_days'
    ];
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
