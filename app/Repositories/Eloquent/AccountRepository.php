<?php

namespace App\Repositories\Eloquent;

use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Models\Client;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
{

    public function getModel(): string
    {
        return Account::class;
    }

    public function getList()
    {
        $result = $this->model->leftJoin('users', 'account.user_edit_id', '=', 'users.id')
            ->leftJoin('contructor', 'account.contractor_id', '=', 'contructor.id')
            ->leftJoin('client', 'account.client_id', '=', 'client.client_id')
            ->leftJoin('bank', 'account.bank_id', '=', 'bank.id')
            ->select(
                'account.*',
                'bank.bank_code as bank_code',
                'users.name as user_name',
                'users.email as user_email',
                'contructor.company_name as contructor_company_name',
                'client.represent_name as client_represent_name',
            );

        foreach ($result as $key => $value) {
            $accountBalance = AccountBalanceHistory::where('account_number', $value['account_number'])->orderBy('date_history', 'desc')->first();
            $result[$key]['balance'] = $accountBalance ? $accountBalance['balance'] : 0;
        }

        return $result->orderBy('created_at', 'desc')->paginate(env('PAGINATE_DEFAULT', 30));
    }


    public function searchAccountNumber($request)
    {
        return $this->model->where('account_number', 'like', '%' . $request['account_number'] . '%')->select('account.id', 'account.account_number')->get();
    }

    public function getListAccountBalances(Request $request)
    {
        $clientId = Client::all('client_id');
        $query = $this->getQuery();
        $query = $query->whereIn('account.client_id', $clientId);
        $query = $query->select([
            'bank_name',
            'account.client_id',
            'represent_name',
            'account.account_number',
            'account_holder',
            'branch_name',
            'representative_account'
        ]);
        $query = $query->rightJoin(
            DB::raw("(select client_id, represent_name from client) as client"),
            'account.client_id',
            '=',
            'client.client_id'
        );
        $query->orderBy('account.client_id');
        $listAccount = $query->get();

        foreach ($listAccount as $account) {
            $balance = AccountBalanceHistory::where('account_number', '=', $account->account_number)
                ->orderBy('date_history', 'desc');
            if ($request->from_date) {
                $toDate = Carbon::createFromFormat('Y-m-d', $request->from_date)->endOfMonth()->format('Y-m-d H:i:s');
                $balance->where('date_history', '<=', $toDate);
            }
            $balance = $balance->first();
            $account->balance = $balance ? $balance->balance : "0.00";
            $account->date_history = $balance ? $balance->date_history : null;
        }
        return $listAccount;
    }

    public function getListAccountBalancesHistory(Request $request)
    {

        $clientId = Client::all('client_id');
        $query = $this->clearQuery();
        $query = $query->whereIn('account.client_id', $clientId);
        if ($request->from_date) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
            $toDate = Carbon::createFromFormat('Y-m-d', $request->from_date)->endOfMonth()->format('Y-m-d H:i:s');
            $query->where('date_history', '>=', $fromDate);
            $query->where('date_history', '<=', $toDate);
        } else {
            $query = $query->where('date_history', '>=', Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'));
        }

        $query = $query->select([
            'bank_name',
            'account.client_id',
            'represent_name',
            'account.account_number',
            'account_holder',
            'branch_name',
            'representative_account',
            'acb.balance as balance',
            'date_history'
        ]);
        $query = $query->rightJoin(
            DB::raw("(select account_number,balance, date_history from account_balance_history
             order by date_history desc) as acb"),
            'account.account_number',
            '=',
            'acb.account_number'
        );

        $query = $query->rightJoin(
            DB::raw("(select client_id, represent_name from client) as client"),
            'account.client_id',
            '=',
            'client.client_id'
        );
        return $query->orderBy('date_history', 'desc')->get();
    }

    public function getAccountNumberByClient($request)
    {
        return $this->model->leftJoin('client', 'account.client_id', '=',  'client.client_id')
            ->select(
                'account.id',
                'account.account_number',
                'account.branch_name',
                'account.bank_name',
                'account.service_type',
                'client.company_name as client_company_name',
            )
            ->where('account.client_id', $request->client_id)
            ->get();
    }

    public function checkUniqueAccountNumber($request)
    {
        if ($request->account_number) {
            $data = $this->model->where('account_number', $request->account_number)->get();
            if ($request->id) {
                $data = $this->model
                    ->where('account_number', $request->account_number)
                    ->where('id', '!=', $request->id)->get();
            }
            return $data->isNotEmpty();
        }
        return;
    }

    public function findById($id)
    {
        $result = $this->model->with('client', 'contractor', 'bank')->find($id);
        $accountBalance = AccountBalanceHistory::where('account_number', $result['account_number'])->orderBy('date_history', 'desc')->first();
        $result['balance'] = $accountBalance ? $accountBalance['balance'] : 0;
        return $result;
    }
}
