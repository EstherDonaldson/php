<?php

namespace App\Repositories;

use App\Models\Assignment;

class AssignmentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
//        $this->list_search_fields = ['fullname', 'email', 'phone'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return Assignment::class;
    }

}
