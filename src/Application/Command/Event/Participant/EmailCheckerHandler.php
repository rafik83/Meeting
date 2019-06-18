<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

use Proximum\Vimeet\Infrastructure\Repository\ParticipantRepository;
use Proximum\Vimeet\Infrastructure\Repository\UserRepository;

class EmailCheckerHandler
{
    public const EMAIL_KNOWN_FROM_EVENT = 'emailFromEvent';
    public const EMAIL_KNOWN_FROM_VIMEET = 'emailFromVimeet';
    public const EMAIL_UNKNOWN = 'emailUnknown';

    /** @var UserRepository */
    private $userRepository;

    /** @var ParticipantRepository */
    private $participantRepository;

    public function __construct(UserRepository $userRepository, ParticipantRepository $participantRepository)
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

        $participant = $this->participantRepository->getAllParticipantForUser($findEmailForFastCheckin->event, $user);

        return null === $participant ? self::EMAIL_KNOWN_FROM_VIMEET : self::EMAIL_KNOWN_FROM_EVENT;
    }
}
