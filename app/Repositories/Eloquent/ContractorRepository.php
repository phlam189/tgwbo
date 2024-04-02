<?php

namespace App\Repositories\Eloquent;

use App\Models\Contractor;
use App\Repositories\Interfaces\ContractorRepositoryInterface;

class ContractorRepository extends BaseRepository implements ContractorRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    const IS_HONSHA = 1;

    public function getModel(): string
    {
        return Contractor::class;
    }

    public function getList($request)
    {
        return $this->model->where('is_honsha', '=', $request->is_honsha ?? 0)->leftJoin('users', 'contructor.user_edit_id', '=', 'users.id')
            ->select('contructor.*', 'users.name as user_name', 'users.email as user_email')
            ->with('parent')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINATE_DEFAULT', 30));
    }

    public function getListId($request)
    {
        return $this->model
            ->where('is_honsha', '=', $request->is_honsha ?? 0)
            ->select('contructor.id', 'contructor.company_name', 'contructor.representative_name')
            ->get();
    }

    public function getContractor()
    {
        return $this->model->get();
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

    public function getAccountContractorById($id)
    {
        return $this->model
            ->select('*')
            ->where('id', '=', $id)
            ->with('parent')
            ->first();
    }

    public function getListContractor($request)
    {
        return $this->model->where('is_honsha', '=', $request->is_honsha ?? 0)->leftJoin('users', 'contructor.user_edit_id', '=', 'users.id')
            ->select('contructor.*', 'users.name as user_name', 'users.email as user_email')
            ->with('parent')
            ->get();
    }

    public function findContractorIsHonsha($id = '')
    {
        return $this->model
            ->where('is_honsha', '=', self::IS_HONSHA)
            ->first();
    }

    public function findContractor($id)
    {
        return $this->model
            ->where('id', $id)
            ->first();
    }
}
