<?php

namespace App\Repositories;

use App\Models\GradeLevel;

class GradeLevelRepository extends BaseRepository
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
        return GradeLevel::class;
    }
}
