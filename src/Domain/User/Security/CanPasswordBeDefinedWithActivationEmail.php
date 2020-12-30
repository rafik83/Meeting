<?php

namespace Proximum\Vimeet\Domain\User\Security;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class CanPasswordBeDefinedWithActivationEmail
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function isSatisfiedBy(Event $event, string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (null === $user) {
            return false;
        }

        if (false === empty($user->getPassword())) {
            return false;
        }

        if ($this->extraParameterRepository->findByEventAndType($event, Type::TYPE_TECH_EVENT_LOGIN_ENABLED)) {
            return false;
        }

        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);

        $isImported = false;

        foreach ($participants as $participant) {
            if ($participant->isImported()) {
                $isImported = true;
                break;
            }
        }

        return $isImported;
    }
}
