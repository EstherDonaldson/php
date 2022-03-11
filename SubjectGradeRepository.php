<?php

namespace App\Repositories;

use App\Models\SubjectGrade;

class SubjectGradeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = array();
        $this->tenant_id = null;
    }

    public function getModel()
    {
        // TODO: Implement getModel() method.
        return SubjectGrade::class;
    }
}
