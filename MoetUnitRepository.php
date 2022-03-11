<?php

namespace App\Repositories;

use App\Models\MoetUnit;

class MoetUnitRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->list_search_fields = array('code', 'name');
        $this->tenant_id = TENANT_ID_DEFAULT;
    }

    public function getModel()
    {
        // TODO: Implement getModel() method.
        return MoetUnit::class;
    }
}
