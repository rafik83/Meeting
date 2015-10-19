<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\User;

interface UserRepositoryInterface
{
    /**
     * @param $email
     *
     * @return boolean
     */
    public function emailExists($email);

    /**
     * @param User $user
     */
    public function add(User $user);
}
