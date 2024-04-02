<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\BusinessException;
use Exception;
use App\Http\Validations\UserValidation;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $userValidation;
    protected $userService;

    public function __construct(UserValidation $userValidation, UserService $userService)
    {
        $this->userValidation = $userValidation;
        $this->userService = $userService;
    }

    public function register(Request $request)
    {
        try {
            $validator = $this->userValidation->checkRegisterValidation(
                $request
            );
            if($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $data = $this->userService->register($request->all());
            return $this->successResponse($data);
        } catch (BusinessException $e) {
            throw $e;
        } catch(Exception $e) {
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function getUserInfo(Request $request)
    {
        try {
            return $this->successResponse($request->user());
        } catch (Exception $e) {
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function getToken(Request $request)
    {
        try {
            $validator = $this->userValidation->getTokenValidation($request);
            if($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $message = null;

            $user = DB::connection('remote')->table('users')
                ->select('id','email', 'active', 'first_name', 'last_name')
                ->where('email', '=', $request->email)->first();
            $isActive = $user ? $user->active : 0;
            if ($isActive) {
                $userLogin = User::firstOrNew([
                    'email' => $request->email
                ]);
                $userLogin->name = $user->first_name.$user->last_name;

                if ($request->role == 'merchant') {
                    $merchant = DB::connection('remote')->table('merchants')
                        ->select('first_name','last_name', 'company', 'email')
                        ->where('user_id', '=', $user->id)->first();
                    $client = \App\Models\Client::firstOrNew([
                        'client_id' => $user->id
                    ]);
                    if ($merchant) {
                        $client->represent_name = $merchant->first_name.$merchant->last_name;
                        $client->company_name = $merchant->company;
                        $client->email = $merchant->email;
                        $client->save();
                    }
                    $userLogin->client_id = $user->id;
                }
                $userLogin->save();
                $data = $this->userService->getToken($request);
            } else {
                $data = [];
                $message=  'The account does not exists or not active';
            }

            return $this->successResponse($data, $message);
        } catch (BusinessException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new BusinessException("EUA000", previous: $e);
        }
    }
}
