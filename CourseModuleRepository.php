<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\SchoolYear;

class CourseModuleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->school_year = SchoolYear::where('tenant_id', TENANT_ID_DEFAULT)->where('status', 1)->first();
        if (empty($this->school_year)) {
            return false;
        }
        $this->school_year_id = $this->school_year->_id;
    }

    public function getModel()
    {
        return CourseModule::class;
    }

    public function getCourseModuleByGame($game_id = null, $return_id = false)
    {
        //Get Course of tenant
        $course_ids = Course::where('tenant_id', TENANT_ID_DEFAULT)->where('school_year_id', $this->school_year)->pluck('_id')->toArray();
        $query = CourseModule::where('object_type', 'game')->whereIn('course_id', $course_ids);
        if (!empty($game_id)) {
            $query = $query->where('object_id', $game_id);
        }
        if ($return_id) {
            return $query->pluck('_id')->toArray();
        }
        return $query->get();
    }
}
