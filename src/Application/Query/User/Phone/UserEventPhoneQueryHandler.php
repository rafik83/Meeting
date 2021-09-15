<?php

namespace Proximum\Vimeet\Application\Query\User\Phone;

use Proximum\Vimeet\Application\Exception\User\Phone\UserEventPhoneNotFoundException;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class UserEventPhoneQueryHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     */
    public function __construct(UserEventPhoneRepositoryInterface $userEventPhoneRepository)
    {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
    }

    /**
     * @param UserEventPhoneQuery $query
     *
     * @throws UserEventPhoneNotFoundException
     *
     * @return UserEventPhone
     */
    public function handle(UserEventPhoneQuery $query)
    {
        $userEventPhone = $this->userEventPhoneRepository->find($query->user, $query->event);

        if (null === $userEventPhone) {
            throw new UserEventPhoneNotFoundException(
                sprintf(
                    'UserEventPhone not found for User %s and Event %s',
                    $query->user->getId(),
                    $query->event->getId()
                )
            );
        }

        return $userEventPhone;
    }
}
