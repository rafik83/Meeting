<?php

namespace Proximum\Vimeet\Domain\UserEvent;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class UserEventPhoneChecker
{
    /**
     * @var UserEventPhoneRepositoryInterface
     */
    private $userEventPhoneRepository;

    /**
     * UserEventPhoneChecker constructor.
     *
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     */
    public function __construct(UserEventPhoneRepositoryInterface $userEventPhoneRepository)
    {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    public function isValidated(User $user, Event $event)
    {
        return null !== $this->getValidatedUserEventPhone($user, $event);
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return null|User\UserEventPhone
     */
    public function getValidatedUserEventPhone(User $user, Event $event): ?User\UserEventPhone
    {
        return $this->userEventPhoneRepository->findValidated($user, $event);
    }
}
