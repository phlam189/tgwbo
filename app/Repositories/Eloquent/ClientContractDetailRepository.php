<?php

namespace App\Repositories\Eloquent;

use App\Models\ClientContractDetail;
use App\Repositories\Interfaces\ClientContractDetailRepositoryInterface;

class ClientContractDetailRepository extends BaseRepository implements ClientContractDetailRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return ClientContractDetail::class;
    }
}
