<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserManager
{
    private UserRepositoryInterface $userRepository;
    private UserEventRepositoryInterface $userEventRepository;
    private ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository;
    private ChangeMailTokenRepositoryInterface $changeMailTokenRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserEventRepositoryInterface $userEventRepository,
        ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository,
        ChangeMailTokenRepositoryInterface $changeMailTokenRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userEventRepository = $userEventRepository;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
        $this->changeMailTokenRepository = $changeMailTokenRepository;
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

    public function fillInformation(User $user, string $firstname, string $lastname)
    {
        $account = new User\Account();
        $account->setFirstName($firstname);
        $account->setLastName($lastname);
        $user->setAccount($account);

        $this->userRepository->set($user);
    }

    public function addUserActivateToken(User $user, Sheet $sheet, string $token)
    {
        $activateAccountToken = new ActivateAccountToken($user, $token, $sheet, new \DateTime('tomorrow'));
        $this->activateAccountTokenRepository->create($activateAccountToken);
    }

    public function addUserEmailToken(User $user, string $email, string $token)
    {
        $changeMailToken = new ChangeMailToken($user, $email, $token, new \DateTime('tomorrow'));
        $this->changeMailTokenRepository->create($changeMailToken);
    }
}
