<?php

namespace App\Repositories;

use App\Models\QuestionCategory;

class QuestionCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
//        $this->list_search_fields = ['fullname', 'email', 'phone'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        return QuestionCategory::class;
    }

    public function genCodeCategory()
    {
        $tenant_id = $this->tenant_id;
        do {
            $code = rand(100000, 999999);
            $category = QuestionCategory::where('code', $code)->where('tenant_id', $tenant_id)->first();
        } while (!is_null($category));

        return $code;
    }
}
