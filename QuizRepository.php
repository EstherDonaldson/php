<?php

namespace App\Repositories;

use App\Helpers\CommonLib;
use App\Models\Quiz;
use App\Transformers\V1\Admin\AssignmentTransformer;
use App\Transformers\V1\Admin\QuizQuestionAnswerTransformer;
use App\Transformers\V1\Admin\QuizQuestionCategoryTransformer;
use App\Transformers\V1\Admin\QuizQuestionTransformer;
use App\Transformers\V1\Admin\QuizTransformer;
use Illuminate\Support\Facades\Auth;

class QuizRepository extends BaseRepository
{
    protected $quizTransfomer;
    protected $quizQuestionTransfomer;
    protected $quizQuestionAnswerTransfomer;
    protected $quizQuestionCategoryTransfomer;
    protected $assignmentTransfomer;
    protected $assignmentDetailRepository;

    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = ['name', 'code'];
        $this->tenant_id = TENANT_ID_DEFAULT;
        $this->quizTransfomer = new QuizTransformer();
        $this->quizQuestionTransfomer = new QuizQuestionTransformer();
        $this->quizQuestionAnswerTransfomer = new QuizQuestionAnswerTransformer();
        $this->quizQuestionCategoryTransfomer = new QuizQuestionCategoryTransformer();
        $this->assignmentTransfomer = new AssignmentTransformer();
        $this->assignmentDetailRepository = new AssignmentDetailRepository();
    }

    public function getModel()
    {
        return Quiz::class;
    }

    public function validateData($data)
    {
        return array(
            "code" => $data['code'],
            "course_id" => $data['course_id'],
            "number_of_time" => empty($data['number_of_time']) ? 0 : $data['number_of_time'],
            "name" => $data['name'],
            "start_date" => $data['start_date'],
            "end_date" => $data['end_date'],
            "time" => $data['time'],
            "score" => $data['score'],
            "sort_index" => empty($data['sort_index']) ? 0 : $data['sort_index'],
            "status" => $data['status'],
            "created_user_id" => Auth::check() ? Auth::user()->_id : ""
        );
    }

    public function makeDataUpdate($data)
    {
        $dataUpdate = [];
        if (isset($data['name']) && trim($data['name']) != '') {
            $dataUpdate['name'] = $data['name'];
        }

        return $dataUpdate;
    }


    public function transformer($quiz)
    {
        $categories = $quiz->quiz_question_category;
        $question = $quiz->quiz_question;
        $result = $this->quizTransfomer->transform($quiz);
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $list_question_in_category = [];
                if (count($question) > 0) {
                    $list_question_in_category_not_transform = $question->where('quiz_question_category_id', $category->id)->all();
                    if (count($list_question_in_category_not_transform) > 0) {
                        $list_question_in_category = $this->quizQuestionTransfomer->transform_collection($list_question_in_category_not_transform);
                    }
                }
                $category = $this->quizQuestionCategoryTransfomer->transform($category);
                $category['question'] = array_values($list_question_in_category);
                $result['category'][] = $category;
            }
        }
        $question_not_exist_category = $question->where('quiz_question_category_id', '');
        if (count($question_not_exist_category) > 0) {
            $category = [
                'id' => "",
                'tenant_id' => "",
                'quiz_id' => "",
                'name' => "",
                'parent_id' => "",
                'sort_index' => "",
                'status' => ""
            ];
            $list_question_not_in_category = $this->quizQuestionTransfomer->transform_collection($question_not_exist_category->all());
            $category['question'] = array_values($list_question_not_in_category);
            $result['category'][] = $category;
        }
        return $result;
    }

    public function transformerWithResult($assignment_info, $quiz)
    {
        $categories = $quiz->quiz_question_category;
        $question = $quiz->quiz_question;
        $result = $this->assignmentTransfomer->transform($assignment_info);

        $assignment_details = $this->assignmentDetailRepository->getData(['assignment_id' => $assignment_info->id]);
        $data_answer = [];
        if (count($assignment_details) > 0) {
            foreach ($assignment_details as $assignment_detail) {
                $data_answer[$assignment_detail->quiz_question_id] = [
                    'answer' => $assignment_detail->answer,
                    'score' => $assignment_detail->score
                ];
            }
        }
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $list_question_in_category = [];
                if (count($question) > 0) {
                    $list_question_in_category_not_transform = $question->where('quiz_question_category_id', $category->id);
                    if (count($list_question_in_category_not_transform) > 0) {
                        $a = 0;
                        foreach ($list_question_in_category_not_transform as $item) {
                            $list_question_in_category[$a] = $this->quizQuestionTransfomer->transform($item);
                            $list_question_in_category[$a]['result_answer'] = empty($data_answer[$item->_id]['answer']) ? null : $data_answer[$item->_id]['answer'];
                            $list_question_in_category[$a]['result_score'] = empty($data_answer[$item->_id]['score']) ? null : $data_answer[$item->_id]['score'];
                            $a++;
                        }
                    }
                }
                $category = $this->quizQuestionCategoryTransfomer->transform($category);
                $category['question'] = array_values($list_question_in_category);
                $result['category'][] = $category;
            }
        }
        $question_not_exist_category = $question->where('quiz_question_category_id', '');
        if (count($question_not_exist_category) > 0) {
            $category = [
                'id' => "",
                'tenant_id' => "",
                'quiz_id' => "",
                'name' => "",
                'parent_id' => "",
                'sort_index' => "",
                'status' => ""
            ];
            $list_question_not_in_category = [];
            $a = 0;
            foreach ($question_not_exist_category as $item1) {
                $list_question_not_in_category[$a] = $this->quizQuestionTransfomer->transform($item1);
                $list_question_not_in_category[$a]['result_answer'] = empty($data_answer[$item1->_id]['answer']) ? null : $data_answer[$item1->_id]['answer'];
                $list_question_not_in_category[$a]['result_score'] = empty($data_answer[$item1->_id]['score']) ? null : $data_answer[$item1->_id]['score'];
                $a++;
            }
            $category['question'] = array_values($list_question_not_in_category);
            $result['category'][] = $category;
        }
        return $result;
    }

    public function makeCodeForQuiz()
    {
        do {
            $code = CommonLib::generateRandomString(8, "QUIZ");
            $quiz = Quiz::where('code', $code)->where('tenant_id', $this->tenant_id)->first();
        } while (!is_null($quiz));

        return $code;
    }
}
