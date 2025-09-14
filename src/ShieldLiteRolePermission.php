<?php

namespace juniyasyos\ShieldLite;

use juniyasyos\ShieldLite\Models\ShieldRole;

trait ShieldLiteRolePermission
{
    public function roles()
    {
        return $this->belongsToMany(config('shield.models.role'), 'shield_role_user', 'user_id', 'role_id')
            ->where('guard', shield()->guard());
    }
}
