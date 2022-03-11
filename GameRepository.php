<?php

namespace App\Repositories;

use App\Models\CourseModule;
use App\Models\Game;

class GameRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = ['name'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return Game::class;
    }

    public function getGameByID($game_id)
    {
        return Game::find($game_id);
    }

    public function getListGame($subject_id = null, $grade_id = null, $keyword_search = null, $ignore_id = [])
    {
        $query = Game::where('tenant_id', $this->tenant_id);
        if (!empty($subject_id)) {
            $query = $query->where('subject_ids', $subject_id);
        }
        if (!empty($grade_id)) {
            $query = $query->where('grade_ids', $grade_id);
        }
        if (!empty($keyword_search)) {
            $query = $query->where('name', 'like', "%$keyword_search%");
        }
        if (!empty($ignore_id)) {
            $query = $query->whereNotIn('_id', $ignore_id);
        }
        return $query->get();
    }

    public function getListGameByCourse($subject_id = null, $grade_id = null, $keyword_search = null, $ignore_id = [])
    {
        $query = Game::where('tenant_id', $this->tenant_id);
        if (!empty($subject_id)) {
            $query = $query->where('subject_ids', 'like', "%$subject_id%");
        }
        if (!empty($grade_id)) {
            $query = $query->where('grade_ids', 'like', "%$grade_id%");
        }
        if (!empty($keyword_search)) {
            $query = $query->where('name', 'like', "%$keyword_search%");
        }
        if (!empty($ignore_id)) {
            $query = $query->whereNotIn('_id', $ignore_id);
        }
        return $query->get();
    }

    public function getListGamesForStudent($student, $subjectId = null, $gradeId = null, $keywordSearch = null)
    {
        $courseRepository = new CourseRepository();
        $courses = $courseRepository->getListCourses($student, [], 'student');
        $courseIds = $courses->pluck('id')->toArray();

        $gamesOfCourses = CourseModule::whereIn('course_id', $courseIds)
            ->where('object_type', 'game')
            ->get();
        $gameIdsOfCourses = $gamesOfCourses->pluck('object_id')->toArray();
        $gameIdsOfCourses = array_unique($gameIdsOfCourses);


        $query = Game::where('tenant_id', $this->tenant_id)->whereIn('_id', $gameIdsOfCourses);

        if (!empty($subjectId)) {
            $query = $query->where('subject_ids', 'like', "%$subjectId%");
        }
        if (!empty($gradeId)) {
            $query = $query->where('grade_ids', 'like', "%$gradeId%");
        }
        if (!empty($keywordSearch)) {
            $query = $query->where('name', 'like', "%$keywordSearch%");
        }

        $courses = $courses->keyBy('id');
        $gamesOfCourses = $gamesOfCourses->keyBy('object_id');
        // mix games with grade and subject name of courses
        return $query->get()->map(function ($game) use ($courses, $gamesOfCourses) {
            $game->grade_id = 0;
            $game->grade_name = '';
            $game->subject_id = 0;
            $game->subject_name = '';
            $game->access_code = '';

            $gameOfCourse = isset($gamesOfCourses[$game->id]) ? $gamesOfCourses[$game->id] : null;
            if (is_null($gameOfCourse)) {
                return $game;
            }

            $course = isset($courses[$gameOfCourse->course_id]) ? $courses[$gameOfCourse->course_id] : null;
            if (is_null($course)) {
                return $game;
            }


            $game->grade_id = $course['grade_id'] . "";
            $game->grade_name = isset($course['grade_name']) ? $course['grade_name'] . "" : '';
            $game->subject_id = $course['subject_id'] . "";
            $game->subject_name = isset($course['subject_name']) ? $course['subject_name'] . "" : '';
            $game->access_code = $gameOfCourse->access_code . "";

            return $game;
        });

    }

    public function createGame($data_create)
    {
        return Game::create($data_create);
    }

    public function updateGame($game_id, $data_update)
    {
        return Game::where('_id', $game_id)->update($data_update);
    }
}
