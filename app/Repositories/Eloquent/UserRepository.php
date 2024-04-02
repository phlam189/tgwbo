<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return User::class;
    }

    public function getUser($email)
    {
        return $this->model->where('email', $email)->first();
    }
    
    
}
