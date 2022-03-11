<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseProgram;
use App\Models\Game;
use App\Models\SchoolYear;

class CourseProgramRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getModel()
    {
        return CourseProgram::class;
    }
}
