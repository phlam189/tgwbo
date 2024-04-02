<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function getUser($email);
}
