<?php

namespace App\Repositories;

use App\Libs\CommonLib;
use App\Models\MoetUnit;
use Carbon\Carbon;

abstract class BaseRepository
{
    protected $list_search_fields;
    protected $tenant_id;
    protected $model_class;
    protected $keyword_search = 'keyword';

    public function __construct()
    {
        $this->list_search_fields = array();//Danh sach field dung de search like
        $this->tenant_id = null;//Neu tenant id khac null, dung cho cac table o core admin
        $this->setModel();
    }

    abstract public function getModel();

    public function setModel()
    {
        $this->model_class = app()->make($this->getModel());
    }

    public function getData($conditions = array(), $with = null, $order_by = array(), $skip = 0, $take = 0, $columns = ['*'], $get_first = false)
    {
//        dd(MoetUnit::where('code', 'like', '%omt%')->orWhere('name', 'like', 'SO')->toSql());
        if (!empty($this->tenant_id) && empty($conditions['tenant_id']))
            $conditions['tenant_id'] = $this->tenant_id;
        $query = $this->model_class->where('deleted_at', null);
        if (isset($conditions[$this->keyword_search])) {
            $keyword_search = $conditions[$this->keyword_search];

            $query->where(function ($query) use ($keyword_search) {
                foreach ($this->list_search_fields as $key => $search_field) {
                    if ($key == 0)
                        $query->where($search_field, 'like', "%$keyword_search%");
                    else
                        $query->orWhere($search_field, 'like', "%$keyword_search%");
                }
            });
            unset($conditions[$this->keyword_search]);
        }
        foreach ($conditions as $condition_key => $condition_item) {
            if (is_null($condition_item) || empty($condition_key)) {
                continue;
            }

            if (is_array($condition_item))
                $query->whereIn($condition_key, $condition_item);
            else
                $query->where($condition_key, $condition_item);
        }

        foreach ($order_by as $order_key => $order_value) {
            $query->orderBy($order_key, $order_value);
        }

        if (!empty($take))
            $query->skip($skip)->take($take);

        if (!empty($with))
            $query->with($with);

        if ($get_first)
            return $query->first();
        return $query->get($columns);
    }

    public function create($data)
    {
        if (!empty($this->tenant_id) && empty($data['tenant_id']))
            $data['tenant_id'] = $this->tenant_id;

        return $this->model_class->create($data);
    }

    public function firstOrCreate($data)
    {
        if (!empty($this->tenant_id) && empty($data['tenant_id']))
            $data['tenant_id'] = $this->tenant_id;

        return $this->model_class->firstOrCreate($data);
    }

    public function updateOrCreate($condition, $data)
    {
        return $this->model_class->updateOrCreate($condition, $data);
    }

    public function update($data, $id)
    {
        $data_info = $this->find($id);
        if (empty($data_info))
            return false;
        else
            return $data_info->update($data);
    }

    public function delete($id)
    {
        $data_info = $this->find($id);
        if (empty($data_info))
            return false;
        else
            return $data_info->delete();
    }

    public function deleteByParam($conditions)
    {
        $query = $this->model_class->where('deleted_at', null);
        foreach ($conditions as $condition_key => $condition_item) {
            if (is_null($condition_item) || empty($condition_key)) {
                continue;
            }

            if (is_array($condition_item))
                $query->whereIn($condition_key, $condition_item);
            else
                $query->where($condition_key, $condition_item);
        }
        $query->delete();
    }

    public function find($id)
    {
        return $this->model_class->find($id);
    }

    public function getFirst()
    {
        return $this->model_class->first();
    }

    public function bulkInsert($data_multiple)
    {
        $data_insert_multiple = array();
        foreach ($data_multiple as $input_data) {
            if (!empty($this->tenant_id) && empty($input_data['tenant_id']))
                $input_data['tenant_id'] = $this->tenant_id;
            $input_data['created_at'] = dateNow();
            $input_data['updated_at'] = dateNow();
            $data_insert_multiple[] = $input_data;
        }

        return $this->model_class->insert($data_insert_multiple);
    }
}
