<?php

namespace App\Repositories\Eloquent;

use App\Models\IntroducerInformation;
use App\Repositories\Interfaces\IntroducerRepositoryInterface;

class IntroducerRepository extends BaseRepository implements IntroducerRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return IntroducerInformation::class;
    }

    public function getList()
    {
        return $this->model->leftJoin('users', 'introducer_infomation.user_edit_id', '=', 'users.id')
            ->leftJoin('client', 'introducer_infomation.client_id', '=', 'client.client_id')
            ->leftJoin('contructor', 'introducer_infomation.account_contractor_id', '=', 'contructor.id')
            ->select(
                'introducer_infomation.*',
                'users.name as user_name',
                'users.email as user_email',
                'client.represent_name as client_represent_name',
                'contructor.representative_name as contructor_representative_name',
            )->with('contructor')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINATE_DEFAULT', 30));
    }

    public function checkUniqueEmail($request)
    {
        if ($request->id) {
            $id = $request->input('id');
            $email = $request->input('email');
            $data = $this->model->where('email', $email)->where('id', '!=', $id)->get();
            return $data->isNotEmpty();
        } else {
            $data = $this->model->where('email', $request->email)->get();
            return $data->isNotEmpty();
        }
    }

    public function showWithContractor($id)
    {
        return $this->model->leftJoin('contructor', 'introducer_infomation.contractor_id', '=', 'contructor.id')
            ->select(
                'introducer_infomation.*',
                'contructor.company_name as contractor_company_name'
            )
            ->find($id);
    }

    public function showDetail($id)
    {
        return $this->model->leftJoin('client', 'introducer_infomation.client_id', '=', 'client.id')
            ->leftJoin('contructor as account_contractor', 'introducer_infomation.account_contractor_id', '=', 'account_contractor.id')
            ->leftJoin('contructor as contractor', 'introducer_infomation.contractor_id', '=', 'contractor.id')
            ->select(
                'introducer_infomation.*',
                'account_contractor.representative_name as account_contractor_representative_name',
                'contractor.company_name as contractor_company_name',
                'client.represent_name as client_represent_name'
            )
            ->find($id);
    }
}
