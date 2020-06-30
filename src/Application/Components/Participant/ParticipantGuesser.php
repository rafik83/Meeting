<?php

namespace Proximum\Vimeet\Application\Components\Participant;

use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ParticipantGuesser
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @throws SheetNotFoundException
     * @throws ParticipantNotFoundException
     */
    public function getUserEventParticipant(User $user, Event $event): Participant
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEventWhereUserIsParticipant($user, $event);

        if (empty($sheets)) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        $sheet = reset($sheets);

        if (!$sheet instanceof Sheet) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        $participant = $sheet->getUserParticipant($user);

        if (!$participant instanceof Participant) {
            throw new ParticipantNotFoundException('Participant not found.');
        }

        return $participant;
    }
}
