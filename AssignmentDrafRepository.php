<?php

namespace App\Repositories;

use App\Models\AssignmentDetail;
use App\Models\AssignmentDraf;

class AssignmentDrafRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return AssignmentDraf::class;
    }

}
