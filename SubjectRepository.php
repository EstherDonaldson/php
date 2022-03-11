<?php

namespace App\Repositories;

use App\Models\Subject;

class SubjectRepository extends BaseRepository
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
        return Subject::class;
    }
}
