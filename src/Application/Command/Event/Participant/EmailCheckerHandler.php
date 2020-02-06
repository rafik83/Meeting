<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class EmailCheckerHandler
{
    public const EMAIL_KNOWN_FROM_EVENT = 'emailFromEvent';
    public const EMAIL_KNOWN_FROM_VIMEET = 'emailFromVimeet';
    public const EMAIL_UNKNOWN = 'emailUnknown';

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(UserRepositoryInterface $userRepository, ParticipantRepositoryInterface $participantRepository)
    {
        $this->userRepository = $userRepository;
        $this->participantRepository = $participantRepository;
    }

    public function handle(EmailChecker $findEmailForFastCheckin): string
    {
        $user = $this->userRepository->findByEmail($findEmailForFastCheckin->email);

        if (null === $user) {
            return self::EMAIL_UNKNOWN;
        }

        $participants = $this->participantRepository->getAllParticipantForUser($findEmailForFastCheckin->event, $user);

        return empty($participants) ? self::EMAIL_KNOWN_FROM_VIMEET : self::EMAIL_KNOWN_FROM_EVENT;
    }
}
