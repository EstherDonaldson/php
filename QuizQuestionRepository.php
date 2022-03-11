<?php

namespace App\Repositories;

use App\Models\QuizQuestion;

class QuizQuestionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
//        $this->list_search_fields = ['fullname', 'email', 'phone'];
    }

    public function getModel()
    {
        return QuizQuestion::class;
    }

}
