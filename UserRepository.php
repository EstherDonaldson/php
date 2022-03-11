<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = ['code', 'username', 'email', 'phone', 'fullname', 'firstname', 'lastname'];
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        // TODO: Implement getModel() method.
        return User::class;
    }

    public function getUserByRole($role_id, $skip, $take, $keyword_search = null, $status = null, $return_total = false, $moet_unit_id = null, $ignore_user_id = [])
    {
        $query = User::whereHas('roleUser', function ($q) use ($role_id, $moet_unit_id) {
            $q->where('role_id', $role_id);
            if (!empty($moet_unit_id)) {
                $q->where('moet_unit_id', $moet_unit_id);
            }
        })->with('roleUser.moetUnit');
        if (!empty($ignore_user_id) && is_array($ignore_user_id)) {
            $query = $query->whereNotIn('_id', $ignore_user_id);
        }
        if (!empty($keyword_search)) {
            $query = $query->where(function ($q) use ($keyword_search) {
                $q->where('fullname', 'like', "%$keyword_search%")
                    ->orWhere('username', 'like', "%$keyword_search%")
                    ->orWhere('phone', 'like', "%$keyword_search%")
                    ->orWhere('email', 'like', "%$keyword_search%");
            });
        }
        if (!is_null($status)) {
            $query = $query->where('status', $status);
        }
        if ($return_total) {
            return $query->count();
        }
        return $query->orderBy('lastname')->skip($skip)->take($take)->get();
    }

    public function getUserByParam($params, $ignore_id = null, $with_data = false, $get_list = false, $keyword_search = null)
    {
        $query = User::where('tenant_id', $this->tenant_id);
        if (!empty($ignore_id)) {
            if (is_array($ignore_id))
                $query = $query->whereNotIn('_id', $ignore_id);
            else
                $query = $query->whereNotIn('_id', [$ignore_id]);

        }
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $query = $query->whereIn($key, $param);
            } else {
                $query = $query->where($key, $param);
            }
        }
        if ($with_data) {
            $query = $query->with('roleUser.moetUnit');
        }
        if (!empty($keyword_search)) {
            $query = $query->where(function ($q) use ($keyword_search) {
                $q->where('fullname', 'like', "%$keyword_search%")
                    ->orWhere('username', 'like', "%$keyword_search%")
                    ->orWhere('phone', 'like', "%$keyword_search%")
                    ->orWhere('email', 'like', "%$keyword_search%");
            });
        }
        if ($get_list)
            return $query->get();
        return $query->first();
    }

    public function create($data)
    {
        return User::create($data);
    }

    public function update($id, $data)
    {
        return User::where("_id", $id)->update($data);
    }

    public function delete($id)
    {
        return User::where("_id", $id)->delete();
    }
}
