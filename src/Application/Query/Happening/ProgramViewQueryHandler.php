<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Exception\Happening\MissingEventDayConfigurationException;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class ProgramViewQueryHandler
{
    /** @var DayRepositoryInterface */
    private $dayRepository;

    /** @var DayViewQueryHandler */
    private $dayViewQueryHandler;

    /** @var HappeningParticipationQueryHandler */
    private $happeningParticipationQueryHandler;

    /** @var MassRepositoryInterface */
    private $massRepository;

    /** @var FullHappeningQueryHandler */
    private $fullHappeningQueryHandler;

    public function __construct(
        DayRepositoryInterface $dayRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationQueryHandler $happeningParticipationQueryHandler,
        MassRepositoryInterface $massRepository,
        FullHappeningQueryHandler $fullHappeningQueryHandler
    ) {
        $this->dayRepository = $dayRepository;
        $this->dayViewQueryHandler = $dayViewQueryHandler;
        $this->happeningParticipationQueryHandler = $happeningParticipationQueryHandler;
        $this->massRepository = $massRepository;
        $this->fullHappeningQueryHandler = $fullHappeningQueryHandler;
    }

    /**
     * @param ProgramViewQuery $programViewQuery
     *
     * @throws MissingEventDayConfigurationException
     *
     * @return ProgramView
     */
    public function handle(ProgramViewQuery $programViewQuery): ProgramView
    {
        $eventDays = $this->dayRepository->findByEvent($programViewQuery->event);

        if (empty($eventDays)) {
            throw new MissingEventDayConfigurationException();
        }

        $masses = [];

        if (null === $programViewQuery->category) {
            $masses = $this->massRepository->findByTypes(
                [$programViewQuery->sheet->getType()],
                $programViewQuery->locale
            );
        }

        $dayViews = [];
        foreach ($eventDays as $day) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $programViewQuery->event,
                    $programViewQuery->sheet,
                    $programViewQuery->user,
                    $day,
                    $programViewQuery->locale,
                    $programViewQuery->category,
                    $masses
                )
            );
        }

        $categoryTitle = null !== $programViewQuery->category
            ? $programViewQuery->category->getTitle($programViewQuery->locale)
            : null;

        $programView = new ProgramView(
            $dayViews,
            $categoryTitle
        );

        $this->happeningParticipationQueryHandler->handle(
            new HappeningParticipationQuery(
                $programView,
                $programViewQuery->sheet,
                $programViewQuery->user
            )
        );

        $this->fullHappeningQueryHandler->handle(
            new FullHappeningQuery(
                $programView,
                $programViewQuery->event
            )
        );

        return $programView;
    }
}
