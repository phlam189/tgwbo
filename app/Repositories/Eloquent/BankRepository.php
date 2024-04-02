<?php

namespace App\Repositories\Eloquent;

use App\Models\Bank;
use App\Repositories\Interfaces\BankRepositoryInterface;

class BankRepository extends BaseRepository implements BankRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return Bank::class;
    }

    public function getList()
    {
        return $this->model->leftJoin('users', 'bank.user_edit_id', '=', 'users.id')
            ->select('bank.*', 'users.name as user_name', 'users.email as user_email')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINATE_DEFAULT', 30));
    }

    public function getListBankName()
    {
        return $this->model->select('bank.id', 'bank.bank_name', 'bank.bank_code')->get();
    }
}
