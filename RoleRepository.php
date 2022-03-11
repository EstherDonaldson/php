<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = array('code', 'name');
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        // TODO: Implement getModel() method.
        return Role::class;
    }

    public function getRoleByCode($role_code)
    {
        $role_info = Role::where('code', $role_code)->where('tenant_id', $this->tenant_id)->first();
        return $role_info;
    }

}
