<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use App\Exceptions\BusinessException;
use Exception;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function register($request)
    {
        return $this->userRepository->create($request);
    }

    public function getToken($request)
    {
        try {
            
            $user = $this->userRepository->getUser($request->email);
            if (! $user || ($request->secret_key !== config('auth.broker_secret'))) {
                throw new BusinessException("EUA_004");
            }
            return $user->createToken($request->email)->plainTextToken;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
        
    }
}
