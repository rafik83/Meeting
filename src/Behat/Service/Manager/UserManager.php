<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserManager
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UserEventRepositoryInterface */
    private $userEventRepository;

    public function __construct(UserRepositoryInterface $userRepository, UserEventRepositoryInterface $userEventRepository)
    {
        $this->userRepository = $userRepository;
        $this->userEventRepository = $userEventRepository;
    }

    public function create(?string $email = null): User
    {
        if (null === $email) {
            $email = sprintf('%s@example.net', uniqid());
        }

        $user  = UserFactory::create($email);

        $this->userRepository->add($user);

        return $user;
    }

    public function createWithEmptyPassword(string $email): User
    {
        $user = UserFactory::createWithEmptyPassword($email);
        $this->userRepository->add($user);

        return $user;
    }


    public function find(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function set(User $user): void
    {
        $this->userRepository->set($user);
    }

    public function addUserEvent(User $user, Event $event, ?Type $type = null): UserEvent
    {
        $userEvent = new UserEvent($user, $event, $type);
        $this->userEventRepository->add($userEvent);

        return $userEvent;
    }
}
