<?php

namespace Proximum\Vimeet\Application\Query\Happening\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ParticipantsAllowedToAccessQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param ParticipantsAllowedToAccessQuery $query
     *
     * @return Participant[]
     */
    public function handle(ParticipantsAllowedToAccessQuery $query): array
    {
        $participants = [];

        foreach ($query->participants as $participant) {
            $userSheets = $this->sheetRepository->getSheetsByUserAndEvent(
                $participant->getUser(),
                $query->happening->getEvent()
            );

            foreach ($userSheets as $sheet) {
                if (in_array($sheet->getType(), $query->happening->getTypes(), true)) {
                    $participants[] = $participant;

                    break;
                }
            }
        }

        return $participants;
    }
}
