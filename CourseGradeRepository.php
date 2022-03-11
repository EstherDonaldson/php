<?php

namespace App\Repositories;

use App\Models\CourseGrade;

class CourseGradeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();

//        $this->tenant_id = empty(session('tenant_id')) ? 1 : session('tenant_id');
    }

    public function getModel()
    {
        return CourseGrade::class;
    }

    public function getCourseGradeByCourseModule($course_module_id, $with_user = false, $keyword_search = null)
    {
        if ($with_user) {
            if (is_array($course_module_id)) {
                $query = CourseGrade::whereIn('course_module_id', $course_module_id)->where('status', 0)->with('user')->with('course_module.game');
                if (!empty($keyword_search)) {
                    $query = $query->whereHas('user', function ($q) use ($keyword_search) {
                        $q->where('fullname', 'like', "%$keyword_search%");
                    });
                }
                return $query->get();
            } else {
                $query = CourseGrade::where('course_module_id', $course_module_id)->where('status', 0)->with('user')->with('course_module.game');
                if (!empty($keyword_search)) {
                    $query = $query->whereHas('user', function ($q) use ($keyword_search) {
                        $q->where('fullname', 'like', "%$keyword_search%");
                    });
                }
                return $query->get();
            }
        }
        if (is_array($course_module_id))
            return CourseGrade::with('course_module.game')->where('status', 0)->whereIn('course_module_id', $course_module_id)->get();
        return CourseGrade::with('course_module.game')->where('status', 0)->where('course_module_id', $course_module_id)->get();
    }

}
