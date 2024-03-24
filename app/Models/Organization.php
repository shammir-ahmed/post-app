<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'card_number',
        'client_type',
        'official_name',
        'license_number',
        'emirate',
        'account_manager',
        'single_use_credit',
        'license_expire_date',
        'office_address',
        'billing_referance',
        'vat_number',
        'vat_status',
        'display_name',
        'website',
        'default_email',
        'main_phone_number',
        'orn_number',
        'rera_expire_date',
        'license_doc',
        'vat_doc',
        'rera_doc',
        'logo',
        'locations',
        'description_en',
        'key_description_en',
        'description_ar',
        'key_description_ar',
    ];
    
    public function users()
    {
        return $this->hasMany(User::class, 'organization_id', 'id');
    }

    public function locations()
    {
        return $this->hasMany(OrganizationLocation::class);
    }
}
