<?php

namespace App\Repositories;

use App\Models\CourseTeam;

class CourseTeamRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->tenant_id = empty(session('tenant_id')) ? 1 : session('tenant_id');
    }

    public function getModel()
    {
        return CourseTeam::class;
    }

}
