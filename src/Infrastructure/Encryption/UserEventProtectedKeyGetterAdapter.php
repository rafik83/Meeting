<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Proximum\Vimeet\Application\Adapter\ProtectedKeyInterface;
use Proximum\Vimeet\Application\Adapter\UserEventProtectedKeyGetterInterface;
use Proximum\Vimeet\Application\Adapter\UserEventProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UserEventProtectedKeyGetterAdapter implements UserEventProtectedKeyGetterInterface
{
    /** @var ExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var ProtectedKeyInterface */
    private $protectedKey;

    /** @var UserEventProtectedKeyPasswordGetterInterface */
    private $userEventProtectedKeyPasswordGetter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        ProtectedKeyInterface $protectedKey,
        UserEventProtectedKeyPasswordGetterInterface $userEventProtectedKeyPasswordGetter,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->protectedKey = $protectedKey;
        $this->userEventProtectedKeyPasswordGetter = $userEventProtectedKeyPasswordGetter;
        $this->dateTime = $dateTime;
    }

    public function getProtectedKeyByEventAndUser(Event $event, User $user): string
    {
        $extraData = $this->userEventExtraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            Type::PROTECTED_KEY,
            $user
        );

        if ($extraData instanceof User\Event\ExtraData) {
            return $extraData->getValue();
        }

        $password = $this->userEventProtectedKeyPasswordGetter->getProtectedKeyPasswordByEventAndUser($event, $user);
        $key = $this->protectedKey->getKeyProtectedByPassword($password);

        $this->userEventExtraDataRepository->add(
            new User\Event\ExtraData($user, $event, Type::PROTECTED_KEY, $key, $this->dateTime)
        );

        return $key;
    }
}
