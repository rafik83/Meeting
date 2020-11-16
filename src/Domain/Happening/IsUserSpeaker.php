<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class IsUserSpeaker
{
    public function __invoke(Happening $happening, User $user): bool
    {
        $speakers = $happening->getSpeakers();
        $userIsSpeaker = false;
        foreach ($speakers as $speaker) {
            if ($user !== $speaker->getUser()) {
                continue;
            }

            $userIsSpeaker = true;
        }

        return $userIsSpeaker;
    }
}
