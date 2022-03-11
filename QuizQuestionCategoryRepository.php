<?php

namespace App\Repositories;

use App\Models\QuizQuestionCategory;

class QuizQuestionCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
//        $this->list_search_fields = ['fullname', 'email', 'phone'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return QuizQuestionCategory::class;
    }

}
