<?php

namespace Proximum\Vimeet\Domain\UserEvent;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;

class TypeResolver
{
    /**
     * @var UserEventRepositoryInterface
     */
    private $userEventRepository;

    /**
     * TypeResolver constructor.
     *
     * @param UserEventRepositoryInterface $userEventRepository
     */
    public function __construct(UserEventRepositoryInterface $userEventRepository)
    {
        $this->userEventRepository = $userEventRepository;
    }

    /**
     * @param User  $user
     * @param Event $event
     * @param Type  $type
     */
    public function resolve(User $user, Event $event, Type $type)
    {
        $userEvent = $this->userEventRepository->getUserEvent($user, $event);

        if (null === $userEvent) {
            $userEvent = new UserEvent($user, $event, $type);
            $this->userEventRepository->add($userEvent);
        } elseif ($userEvent->getType() !== $type) {
            $userEvent->setType($type);
            $this->userEventRepository->set($userEvent);
        }
    }
}
