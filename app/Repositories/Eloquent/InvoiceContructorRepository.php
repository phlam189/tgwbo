<?php

namespace App\Repositories\Eloquent;

use App\Models\InvoiceContructor;
use App\Repositories\Interfaces\InvoiceContructorRepositoryInterface;

class InvoiceContructorRepository extends BaseRepository implements InvoiceContructorRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return InvoiceContructor::class;
    }

    public function getList($request)
    {
        return $this->model
            ->rightJoin('contructor', 'invoice_contructors.contructor_id', '=', 'contructor.id')
            ->where('contructor.id', $request->contractor_id)
            ->orderBy('created_at', 'desc')
            ->select(
                'invoice_contructors.*',
                'contructor.invoice_prefix',
                'contructor.representative_name',
                'contructor.address',
                'contructor.email',
                'contructor.company_name',
            )
            ->first();
    }

    public function getListByContractorId($request, $date)
    {
        return $this->model
            ->where('contructor_id', $request->contractor_id)
            ->whereMonth('invoice_date', $date->format('m'))
            ->whereYear('invoice_date', $date->format('Y'))
            ->get();
    }

    public function findByNumber($request){
        return $this->model->where('number', $request->number)->first();
    }
}
