<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantFactory
{
    public static function create(Sheet $sheet, User $user = null, \DateTimeInterface $registrationDate = null)
    {
        $user = (null !== $user) ? $user : new User('user@vimeet.com', 'salt', 'password', 'fr');

        $participant = new Participant($sheet, $user, [], true, $registrationDate ?? new \DateTime());
        $sheet->addParticipant($participant);

        return $participant;
    }

    public static function createImported(
        Sheet $sheet,
        User $user = null,
        \DateTimeInterface $registrationDate = null
    ): Participant {
        $participant = self::create($sheet, $user, $registrationDate);
        $participant->setImported(true);

        return $participant;
    }
}
