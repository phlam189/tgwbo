<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return Client::class;
    }

    public function getList()
    {
        $contractDetails = DB::table('client_contract_detail')
            ->select('client_id', DB::raw('SUM(DISTINCT service_type) AS total_service_type'))
            ->groupBy('client_id');

        return $this->model
            ->leftJoin('users', 'client.user_edit_id', '=', 'users.id')
            ->leftJoinSub($contractDetails, 'contract_details', function (JoinClause $join) {
                $join->on('client.client_id', '=', 'contract_details.client_id');
            })
            ->select(
                'client.*',
                'users.name as user_name',
                'users.email as user_email',
                DB::raw('(CASE
                    WHEN contract_details.total_service_type = 1 THEN "Deposit"
                    WHEN contract_details.total_service_type = 2 THEN "Withdrawal"
                    WHEN contract_details.total_service_type = 3 THEN "Both"
                    ELSE null
                    END) AS contract_use_service
                ')
            )
            ->with('contractor')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINATE_DEFAULT', 30));
    }

    public function getClient($isDeleted = false)
    {
        if ($isDeleted) {
            return $this->model->select('client.id', 'client.client_id', 'client.represent_name', 'client.company_name')->withTrashed()->get();
        } else {
            return $this->model->select('client.id', 'client.client_id', 'client.represent_name', 'client.company_name')->get();
        }

    }

    public function checkUniqueEmail($request)
    {
        if ($request->email) {
            $data = $this->model->where('email', $request->email)->get();
            if ($request->id) {
                $data = $this->model
                    ->where('email', $request->email)
                    ->where('id', '!=', $request->id)->get();
            }
            return $data->isNotEmpty();
        }
        return;
    }

    public function showWithContractor($id)
    {
        $contractDetails = DB::table('client_contract_detail')
            ->select('client_id', DB::raw('SUM(DISTINCT service_type) AS total_service_type'))
            ->groupBy('client_id');

        return $this->model->leftJoin('users', 'client.user_edit_id', '=', 'users.id')
            ->leftJoin('contructor', 'client.contractor_id', '=', 'contructor.id')
            ->leftJoinSub($contractDetails, 'contract_details', function (JoinClause $join) {
                $join->on('client.client_id', '=', 'contract_details.client_id');
            })->select(
                'client.*',
                'users.name as user_name',
                'users.email as user_email',
                'contructor.company_name as contractor_company_name',
                DB::raw('(CASE
            WHEN contract_details.total_service_type = 1 THEN "Deposit"
            WHEN contract_details.total_service_type = 2 THEN "Withdrawal"
            WHEN contract_details.total_service_type = 3 THEN "Both"
            ELSE null
            END) AS contract_use_service')
            )->find($id);
    }

    public function findClient($id)
    {
        return $this->model->where('client.id', $id)
            ->with('client_details', 'expense')
            ->first();
    }

    public function checkUniqueClientId($request)
    {
        if ($request->client_id) {
            $data = $this->model->where('client_id', $request->client_id)->get();
            if ($request->id) {
                $data = $this->model
                    ->where('client_id', $request->client_id)
                    ->where('id', '!=', $request->id)->get();
            }
            return $data->isNotEmpty();
        }
        return;
    }

    public function findById($id)
    {
        $contractDetails = DB::table('client_contract_detail')
            ->select('client_id', DB::raw('SUM(DISTINCT service_type) AS total_service_type'))
            ->groupBy('client_id');

        return $this->model->leftJoin('users', 'client.user_edit_id', '=', 'users.id')
            ->leftJoinSub($contractDetails, 'contract_details', function (JoinClause $join) {
                $join->on('client.client_id', '=', 'contract_details.client_id');
            })->select(
                'client.*',
                'users.name as user_name',
                'users.email as user_email',
                DB::raw('(CASE
                WHEN contract_details.total_service_type = 1 THEN "Deposit"
                WHEN contract_details.total_service_type = 2 THEN "Withdrawal"
                WHEN contract_details.total_service_type = 3 THEN "Both"
                ELSE null
                END) AS contract_use_service
            ')
            )->find($id);
    }
}
