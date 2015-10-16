<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\User;

interface UserRepositoryInterface
{
    public function emailExists($email);

    public function add(User $user);
}
