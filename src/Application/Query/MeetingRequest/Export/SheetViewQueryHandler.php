<?php

namespace Proximum\Vimeet\Application\Query\MeetingRequest\Export;

use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Category;

class SheetViewQueryHandler
{
    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesserCache $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesserCache $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView
     */
    public function handle(SheetViewQuery $query): SheetView
    {
        $categoryTitle = '';
        $categories = $query->sheet->getType()->getCategories()->toArray();

        /** @var Category $category */
        foreach ($categories as $category) {
            $categoryTitle = $category->getTitle($query->locale);

            break;
        }

        $participantIds   = [];
        $participantNames = [];

        foreach ($query->participants as $participant) {
            $participantNames[] = $this->participantInfoGuesser->guessParticipantCompleteName(
                $participant,
                $query->locale
            );
            $participantIds[] = $participant->getId();
        }

        return new SheetView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $query->sheet->getType()->getTitle($query->locale),
            $categoryTitle,
            $participantIds,
            $participantNames
        );
    }
}
