<?php

namespace App\Repositories;

use App\Models\MoetUnit;
use App\Models\RoleUser;

class RoleUserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->tenant_id = null;
    }

    public function getModel()
    {
        // TODO: Implement getModel() method.
        return RoleUser::class;
    }

    public function create($data_create)
    {
        return RoleUser::create($data_create);
    }

    public function deleteByUser($user_id)
    {
        return RoleUser::where('user_id', $user_id)->delete();
    }

    public function getMoetUnitOfUser($userId, $moetLevel = MOET_LEVEL_SCHOOL)
    {
        $unitIds = RoleUser::where('user_id', $userId)
            ->get()->pluck('moet_unit_id')->toArray();
        return MoetUnit::whereIn('_id', $unitIds)
            ->where('moet_level', $moetLevel)
            ->where('status', 1)
            ->first();
    }
}
