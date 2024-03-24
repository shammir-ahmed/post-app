<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use ReflectionClass;
use App\Observers\UserObserver;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Authorizable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'api';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $fillable = [
        'client_number',
        'first_name',
        'last_name',
        'username',
        'password',
        'email',
        'phone',
        'gender',
        'country',
        'city',
        'zip',
        'status',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = [
        'full_name',
        'scopes',
        'all_roles',
        'cover',
    ];

    protected $with = [
        'permissions',
        'roles'
    ];

    private static $status = [
        'unverified',
        'pending',
        'active',
        'suspend',
        'cancel'
    ];

    private static $gender = [
        'none',
        'male',
        'female'
    ];

    const STATUS_UNVERIFIED = 'unverified';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspend';

    public function notice()
    {
        $this->hasMany(NoticeVisible::class);
    }

    public function isAdministrator()
    {
        return $this->hasRole('administrator');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'id', 'organization_id');
    }

    public function getFullNameAttribute()
    {
        return "$this->first_name $this->last_name";
    }

    public function getScopesAttribute()
    {
        return $this->getAllPermissions()->pluck('name');
    }

    public function getAllRolesAttribute()
    {
        return $this->getRoleNames();
    }

    /**
    * Get the identifier that will be stored in the subject claim of the JWT.
    *
    * @return mixed
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function meta()
    {
        return $this->hasMany(Meta::class);
    }

    public function getCoverAttribute($value)
    {
        $cover = [];
        $disk = config('filesystems.default');
        $configOfDisk = config('filesystems.disks')[$disk];
        // if (!Storage::disk($disk)->exists("avatar/" . $this->id . "/cover/cover.jpg")) {
        //     $cover['main'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/cover/cover.jpg';
        //     $cover['900x300'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/cover/900x300-cover.jpg';

        //     return $cover;
        // }

        // if ($disk === 's3') {
        //     $cover['main'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/cover/cover.jpg';
        //     $cover['900x300'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/cover/900x300-cover.jpg';
        // }

        // if ($disk === 'local') {
        //     $cover['main'] = env('APP_URL') . '/avatar/' . $this->id.'/cover/cover.jpg';
        //     $cover['900x300'] = env('APP_URL') . '/avatar/' . $this->id.'/cover/900x300-cover.jpg';
        // }

        $cover['main'] = env('APP_URL') . '/avatar/' . $this->id.'/cover/cover.jpg';
        $cover['900x300'] = env('APP_URL') . '/avatar/' . $this->id.'/cover/900x300-cover.jpg';

        return $cover;
    }

    public function getAvatarAttribute($value)
    {
        $avatar = [];
        $disk = config('filesystems.default');
        $configOfDisk = config('filesystems.disks')[$disk];
        // if (!Storage::disk($disk)->exists("avatar/" . $this->id . "/avatar.jpg")) {
        //     $avatar['main'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/avatar.jpg';
        //     $avatar['300x300'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/300x300-avatar.jpg';
        //     $avatar['100x100'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/100x100-avatar.jpg';
        //     $avatar['60x60'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/60x60-avatar.jpg';
        //     $avatar['24x24'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/default/24x24-avatar.jpg';

        //     return $avatar;
        // }

        if ($disk === 's3') {
            $avatar['main'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/avatar.jpg';
            $avatar['300x300'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/300x300-avatar.jpg';
            $avatar['100x100'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/100x100-avatar.jpg';
            $avatar['60x60'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/60x60-avatar.jpg';
            $avatar['24x24'] = 'https://' . $configOfDisk['bucket']. '.' . $configOfDisk['driver']. '.' . $configOfDisk['region'] . '.amazonaws.com/avatar/'.$this->id.'/24x24-avatar.jpg';
        }

        if ($disk === 'local') {
            // $avatar['main'] = env('APP_URL') . '/avatar/' . $this->id.'/avatar.jpg';
            // $avatar['300x300'] = env('APP_URL') . '/avatar/' . $this->id.'/300x300-avatar.jpg';
            // $avatar['100x100'] = env('APP_URL') . '/avatar/' . $this->id.'/100x100-avatar.jpg';
            // $avatar['60x60'] = env('APP_URL') . '/avatar/' . $this->id.'/60x60-avatar.jpg';
            // $avatar['24x24'] = env('APP_URL') . '/avatar/' . $this->id.'/24x24-avatar.jpg';

            $avatar['main'] = env('APP_URL') . '/avatar/' . $this->id.'/avatar.jpg';
            $avatar['300x300'] = $this->get_gravatar($this->email, 300);
            $avatar['100x100'] = $this->get_gravatar($this->email, 100);
            $avatar['60x60'] = env('APP_URL') . '/avatar/' . $this->id.'/60x60-avatar.jpg';
            $avatar['24x24'] = env('APP_URL') . '/avatar/' . $this->id.'/24x24-avatar.jpg';
        }

        return $avatar;
    }

    protected function get_gravatar($email, $s = 40, $d = 'mp', $r = 'g', $img = false, $atts = array())
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }


    public function toSimple()
    {
        return $this->attributesToArray();
    }

    public static function getVariables()
    {
        $expectedAttrs = [
            'status',
            'gender'
        ];

        return (new Collection(
            (new ReflectionClass(self::class))->getStaticProperties()
        ))->filter(function ($value, $key) use ($expectedAttrs) {
            return in_array($key, $expectedAttrs);
        });
    }
}
