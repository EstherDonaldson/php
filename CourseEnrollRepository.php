<?php

namespace App\Repositories;

use App\Models\CourseEnroll;

class CourseEnrollRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = ['fullname', 'email', 'phone'];
    }

    public function getModel()
    {
        return CourseEnroll::class;
    }

    public function studentEnrollCourse($userId, $enrollCode){

        $roleRepository = new RoleRepository();
        $studentRole = $roleRepository->getRoleByCode('student');
        if (empty($studentRole)) {
            return false;
        }
        $enroll = CourseEnroll::where('enroll_code',$enrollCode)
            ->where('role_id',$studentRole->_id)
            ->whereNull('user_id')
            ->first();

        if(is_null($enroll)){
            return false;
        }

        $enroll->update([
            'user_id' => $userId
        ]);

        return $enroll;
    }

}
