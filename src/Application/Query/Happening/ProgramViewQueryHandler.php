<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Exception\Happening\MissingEventDayConfigurationException;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

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

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        DayRepositoryInterface $dayRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationQueryHandler $happeningParticipationQueryHandler,
        MassRepositoryInterface $massRepository,
        FullHappeningQueryHandler $fullHappeningQueryHandler,
        GetTimezoneHelper $getTimezoneHelper,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->dayRepository = $dayRepository;
        $this->dayViewQueryHandler = $dayViewQueryHandler;
        $this->happeningParticipationQueryHandler = $happeningParticipationQueryHandler;
        $this->massRepository = $massRepository;
        $this->fullHappeningQueryHandler = $fullHappeningQueryHandler;
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->isParticipantVisio = $isParticipantVisio;
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

        $timeZone = $this->getTimezoneHelper->getTimezoneByEventAndUser($programViewQuery->event, $programViewQuery->user);

        $timeZonedDays = $this->getTimezonedDays($eventDays, $timeZone);
        $translatedTimeZone = $this->getTimezoneHelper->getTimezoneTranslated($timeZone);

        $masses = [];

        if (null === $programViewQuery->category) {
            $masses = $this->massRepository->findByTypes(
                [$programViewQuery->sheet->getType()],
                $programViewQuery->locale
            );
        }

        $dayViews = [];
        foreach ($timeZonedDays as $timeRange) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $programViewQuery->event,
                    $programViewQuery->sheet,
                    $programViewQuery->user,
                    $timeRange,
                    $programViewQuery->locale,
                    $programViewQuery->category,
                    $masses
                )
            );
        }

        $categoryTitle = null !== $programViewQuery->category
            ? $programViewQuery->category->getTitle($programViewQuery->locale)
            : null;

        $showTimeZone = $this->showTimeZone($programViewQuery->sheet, $programViewQuery->user);

        $programView = new ProgramView(
            $showTimeZone,
            $translatedTimeZone,
            $timeZone,
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

    /**
     * @param Event\Day[] $eventDays
     * @param string      $timezone
     *
     * @return TimeRangeView[]
     */
    private function getTimezonedDays(array $eventDays, string $timezone): array
    {
        $timezonedTimeRangeViews = [];

        foreach ($eventDays as $day) {
            $timezonedTimeRangeViews[] = new TimeRangeView(
                DaysHelper::cloneDateTime($day->getBegin(), $timezone),
                DaysHelper::cloneDateTime($day->getEnd(), $timezone)
            );
        }

        return DaysHelper::splitDays($timezonedTimeRangeViews);
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    private function showTimeZone(Sheet $sheet, User $user): bool
    {
        $participant = $sheet->getUserParticipant($user);

        return $participant && $this->isParticipantVisio->isSatisfiedBy($participant) && $participant->getTimezone();
    }
}
