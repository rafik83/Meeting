<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UserPayloadBuilder
{
    /** @var RouterInterface */
    private $routerAdapter;
    /**
     * @var ParticipantInfoGuesserCache
     */
    private $participantInfoGuesserCache;
    /**
     * @var string
     */
    private $locale;

    public function __construct(
        RouterInterface $routerAdapter,
        ParticipantInfoGuesserCache $participantInfoGuesserCache,
        string $locale
    ) {
        $this->routerAdapter = $routerAdapter;
        $this->participantInfoGuesserCache = $participantInfoGuesserCache;
        $this->locale = $locale;
    }

    public function get(Sheet $sheet, User $user): array
    {
        $avatar = $user->getAvatar();
        if ($avatar === null) {
            $avatar = $this->routerAdapter->generate(
                'event_chat_avatar',
                ['name' => $user->getAccount()->getCompleteName()]
            );
        }

        $position = '';
        $sheetParticipant = $sheet->getUserParticipant($user);
        if ($sheetParticipant) {
            $position = $this->participantInfoGuesserCache->guessParticipantPosition(
                $sheetParticipant,
                $this->locale
            );
        }

        return [
            'userId' => $user->getId(),
            'eventId' => $sheet->getEvent()->getId(),
            'userLastName' => $user->getLastName(),
            'userFirstName' => $user->getFirstName(),
            'userPosition' => $position,
            'userAvatar' => $avatar,
            'userCompany' => $sheet->getTitle(),
        ];
    }
}
