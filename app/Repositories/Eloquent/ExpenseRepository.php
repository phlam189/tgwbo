<?php

namespace App\Repositories\Eloquent;

use App\Models\ExpenseInformation;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return ExpenseInformation::class;
    }

    public function getList()
    {
        return $this->model->leftJoin('users', 'expense_information.user_edit_id', '=', 'users.id')
            ->select(
                'expense_information.*',
                'users.name as user_name',
                'users.email as user_email',
            )->with('client.account')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINATE_DEFAULT', 30));
    }

    public function getExpenseById($id)
    {
        return $this->model
            ->select(
                'expense_information.*',
            )->with('client.account')
            ->where('expense_information.id', $id)
            ->first();
    }
}
