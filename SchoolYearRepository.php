<?php

namespace App\Repositories;

use App\Models\SchoolYear;

class SchoolYearRepository extends BaseRepository
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
        return SchoolYear::class;
    }

    public function getCurrentSchoolYear($tenant_id)
    {
        if (empty($tenant_id))
            $tenant_id = $this->tenant_id;
        $school_year = SchoolYear::where('status', 2)->where('tenant_id', $tenant_id)->first();
        return $school_year;
    }
}
