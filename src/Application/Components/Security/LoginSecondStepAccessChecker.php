<?php

namespace Proximum\Vimeet\Application\Components\Security;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class LoginSecondStepAccessChecker
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /**
     * @param UserRepositoryInterface           $userRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->userRepository = $userRepository;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @param Event  $event
     * @param string $email
     *
     * @return bool
     */
    public function allowedToAccess(Event $event, string $email): bool
    {
        if ($this->userRepository->emailExists($email)) {
            return true;
        }

        return null !== $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED);
    }
}
