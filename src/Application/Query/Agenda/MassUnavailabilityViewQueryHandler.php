<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class MassUnavailabilityViewQueryHandler
{
    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /**
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     * @param MeetingPublishedAccessChecker     $meetingPublishedAccessChecker
     */
    public function __construct(
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker
    ) {
        $this->massAssignmentRepository      = $massAssignmentRepository;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
    }

    /**
     * @param MassUnavailabilityViewQuery $query
     *
     * @return MassUnavailabilityView|null
     */
    public function handle(MassUnavailabilityViewQuery $query)
    {
        $begin = $query->mass->getBegin();
        $end   = $query->mass->getEnd();

        if ($query->mass->isDispatch()) {
            if ($this->meetingPublishedAccessChecker->allowedToAccess($query->event)) {
                $assignment = $this->massAssignmentRepository->find($query->mass, $query->participant);

                if (null !== $assignment) {
                    if (!$assignment->isEnabled()) {
                        return null;
                    }

                    $begin = $assignment->getBegin();
                    $end   = $assignment->getEnd();
                }
            }
        }

        return new MassUnavailabilityView(
            $query->mass->getId(),
            $begin,
            $end,
            $query->mass->getTitle($query->locale),
            $query->mass->getDescription($query->locale),
            $query->mass->getCategory()->getPicto(),
            $query->mass->getCategory()->getLeftColor(),
            $query->mass->getCategory()->getRightColor(),
            $query->event->getTimeZone(),
            $query->mass->isBlocking()
        );
    }
}
