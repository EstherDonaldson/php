<?php

namespace App\Repositories;

use App\Entities\FileEntity;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\UTCDateTime;

class CourseRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = ['name', 'code'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return Course::class;
    }

    public function create($data)
    {

        return parent::create($data);
    }

    public function validateData($data)
    {

        if (!empty(validateEmptyData($data, ['tenant_id', 'name']))) {
            return false;
        }
        $code = $this->makeCodeForCourse();

        return array(
            "tenant_id"       => $data['tenant_id'],
            "sis_id"          => isset($data['sis_id']) ? $data['sis_id'] : null,
            "code"            => $code,
            "name"            => $data['name'],
            "image"           => $this->makeImage($data, $code),
            "description"     => isset($data['description']) ? $data['description'] : '',
            "grade_id"        => isset($data['grade_id']) ? $data['grade_id'] : null,
            "subject_id"      => isset($data['subject_id']) ? $data['subject_id'] : null,
            "school_year_id"  => isset($data['school_year_id']) ? $data['school_year_id'] : null,
            "moet_unit_id"    => isset($data['moet_unit_id']) ? $data['moet_unit_id'] : null,
            "start_date"      => isset($data['start_date']) ? new UTCDateTime(Carbon::parse($data['start_date'])) : null,
            "end_date"        => isset($data['end_date']) ? new UTCDateTime(Carbon::parse($data['end_date'])) : null,
            "status"          => 1,
            "created_user_id" => Auth::check() ? Auth::user()->_id : ""
        );
    }

    public function makeCodeForCourse()
    {
        do {
            $code = rand(100000, 999999);
            $course = Course::where('code', $code)->first();
        } while (!is_null($course));

        return $code;
    }

    public function makeImage($data, $code)
    {

        $imageDefault = '/import/course-default.jpg';

        if (!isset($data['image'])) {
            return $imageDefault;
        }
        $filename = $code . '-' . Carbon::now()->timestamp . '-' . randomString(6);
        if (is_string($data['image'])) {
            $path = (new FileEntity())->saveFileAmazonBase64($data['image'], $filename, 'learning-games/course');
        } else {
            $path = (new FileEntity())->saveFileAmazon($data['image'], $filename, 'learning-games/course');
        }

        return $path ? $path : $imageDefault;
    }

    public function transformCourse(Course $course)
    {
        if (isset($course->start_date)) {
            $startDate = $course->start_date instanceof UTCDateTime ?
                (double)convertUTCDateTimeToTimestamp($course->start_date) :
                (double)Carbon::parse($course->start_date)->timestamp;
        } else {
            $startDate = 0;
        }

        if (isset($course->end_date)) {
            $endDate = $course->end_date instanceof UTCDateTime ?
                (double)convertUTCDateTimeToTimestamp($course->end_date) :
                (double)Carbon::parse($course->end_date)->timestamp;
        } else {
            $endDate = 0;
        }

        return array(
            "id"              => $course->_id . "",
            "tenant_id"       => isset($course->tenant_id) ? $course->tenant_id . "" : null,
            "sis_id"          => isset($course->sis_id) ? $course->sis_id . "" : null,
            "code"            => $course->code . "",
            "name"            => $course->name . "",
            "image"           => isset($course->image) ? asset($course->image) : asset('/'),
            "description"     => isset($course->description) ? $course->description . "" : '',
            "grade_id"        => isset($course->grade_id) ? $course->grade_id . "" : null,
            "subject_id"      => isset($course->subject_id) ? $course->subject_id . "" : null,
            "school_year_id"  => isset($course->school_year_id) ? $course->school_year_id . "" : null,
            "moet_unit_id"    => isset($course->moet_unit_id) ? $course->moet_unit_id . "" : null,
            "start_date"      => $startDate,
            "end_date"        => $endDate,
            "status"          => isset($course->status) ? (integer)$course->status : 0,
            "created_user_id" => isset($course->created_user_id) ? $course->created_user_id . "" : "",
        );
    }

    public function getCourseById($courseId)
    {
        return Course::where('_id', $courseId)->with(['subject', 'grade'])->first();
    }

    public function makeDataUpdate($data)
    {
        $dataUpdate = [];
        if (isset($data['name']) && trim($data['name']) != '') {
            $dataUpdate['name'] = $data['name'];
        }

        if (isset($data['description'])) {
            $dataUpdate['description'] = $data['description'];
        }

        if (isset($data['start_date'])) {
            $dataUpdate['start_date'] = (double)$data['start_date'];
        }

        if (isset($data['end_date'])) {
            $dataUpdate['end_date'] = (double)$data['end_date'];
        }

        if (isset($data['image'])) {
            $dataUpdate['image'] = $this->makeImage($data, 'UPDATE-IMAGE-');
        }

        return $dataUpdate;
    }

    public function getListCourses($user, $dataSearch = [], $type = 'teacher')
    {
        $teacherRole = Role::where('code', 'teacher')->first();
        $studentRole = Role::where('code', 'student')->first();
        if (is_null($teacherRole) || is_null($studentRole)) {
            return [];
        }

        $roleId = $type == 'teacher' ? $teacherRole->_id : $studentRole->_id;

        $courseIds = CourseEnroll::where('user_id', $user->_id)
            ->where('role_id', $roleId)
            ->get()->pluck('course_id')->toArray();

        $courses = Course::whereIn('_id', $courseIds);
        if (isset($dataSearch['grade_id'])) {
            $courses = $courses->where('grade_id', $dataSearch['grade_id']);
        }
        if (isset($dataSearch['subject_id'])) {
            $courses = $courses->where('subject_id', $dataSearch['subject_id']);
        }

        $grades = Grade::get()->keyBy('id');
        $subjects = Subject::get()->keyBy('id');


        return $courses->with(['schoolYear', 'moetUnit', 'enrolls'])
            ->get()->map(function ($course) use ($studentRole, $user, $grades, $subjects) {
                $transform = $this->transformCourse($course);
                $students = $course->enrolls->filter(function ($enroll) use ($studentRole) {
                    return $enroll->role_id == $studentRole->_id;
                });
                $transform['count_students'] = count($students);
                $transform['school_name'] = !is_null($course->moetUnit) ? $course->moetUnit->name . "" : trans('course.no_school');
                $transform['school_year_name'] = !is_null($course->schoolYear) ? $course->schoolYear->name . "" : '';
                $transform['teacher_name'] = $user->fullname . "";
                $transform['can_edit'] = 1;
                $transform['can_delete'] = 1;
                $transform['grade_name'] = isset($grades[$course->grade_id]) ? $grades[$course->grade_id]->name : '';
                $transform['subject_name'] = isset($subjects[$course->subject_id]) ? $subjects[$course->subject_id]->name : '';

                return $transform;
            });


    }

    public function enrollTeacher(Course $course, User $user)
    {
        $teacherRole = Role::where('code', 'teacher')->first();
        if (is_null($teacherRole)) {
            return false;
        }

        $courseEnrollRepository = new CourseEnrollRepository();

        return $courseEnrollRepository->create([
            'course_id'      => $course->_id,
            'fullname'       => $user->fullname,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'user_id'        => $user->_id,
            'role_id'        => $teacherRole->_id,
            'course_team_id' => "",
            'enroll_code'    => $this->generateEnrollCode(),
        ]);

    }

    private function generateEnrollCode()
    {
        do {
            $code = rand(100000, 999999);
            $course = CourseEnroll::where('enroll_code', $code)->first();
        } while (!is_null($course));

        return $code;
    }
}
