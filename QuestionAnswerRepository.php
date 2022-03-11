<?php

namespace App\Repositories;

use App\Models\QuestionAnswer;

class QuestionAnswerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
//        $this->list_search_fields = ['fullname', 'email', 'phone'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return QuestionAnswer::class;
    }

}
