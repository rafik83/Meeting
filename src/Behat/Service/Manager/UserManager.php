<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserManager
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param null $email
     *
     * @return User
     */
    public function create($email = null)
    {
        $email = sprintf('%s@example.net', uniqid());
        $user  = UserFactory::create($email);

        $this->userRepository->add($user);

        return $user;
    }
}
