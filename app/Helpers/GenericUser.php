<?php

namespace App\Helpers;

use Illuminate\Auth\GenericUser as AuthGenericUser;

class GenericUser extends AuthGenericUser
{
    use ACL;

    // /**
    //  * Create a new generic User object.
    //  *
    //  * @param  array  $attributes
    //  * @return void
    //  */
    // public function __construct(array $attributes)
    // {
    //     parent::__construct($attributes);
    // }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    public function recordableAttributes()
    {
        $attributes = [];

        $genericUserAttributes = $this->toArray();

        foreach (['id', 'username', 'full_name', 'email'] as $attr) {
            if (isset($genericUserAttributes[$attr])) {
                $attributes[$attr] = $genericUserAttributes[$attr];
            }
        }

        return $attributes;
    }
}
