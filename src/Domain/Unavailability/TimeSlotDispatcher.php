<?php

namespace Proximum\Vimeet\Domain\Unavailability;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Domain\Unavailability\Mass\IsMassUnavailabilityAssignedToAllTypes;

class TimeSlotDispatcher
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var MassRepositoryInterface */
    private $massRepository;

    /** @var MassAssignmentRepositoryInterface */
    private $massAssignmentRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var IsMassUnavailabilityAssignedToAllTypes */
    private $isMassUnavailabilityAssignedToAllTypes;

    /** @var JobQueueInterface */
    private $jobQueueInterface;

    /** @var User[] */
    private $usersDispatched = [];

    public function __construct(
        UserRepositoryInterface $userRepository,
        MassRepositoryInterface $massRepository,
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        TypeRepositoryInterface $typeRepository,
        IsMassUnavailabilityAssignedToAllTypes $isMassUnavailabilityAssignedToAllTypes,
        JobQueueInterface $jobQueueInterface
    ) {
        $this->userRepository = $userRepository;
        $this->massRepository = $massRepository;
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->typeRepository = $typeRepository;
        $this->isMassUnavailabilityAssignedToAllTypes = $isMassUnavailabilityAssignedToAllTypes;
        $this->jobQueueInterface = $jobQueueInterface;
    }

    /**
     * @throws UnableToDispatchException
     */
    public function dispatchAll(Event $event)
    {
        $this->usersDispatched = [];

        $unavailabilities = $this->massRepository->findDispatchByEvent($event);

        foreach ($unavailabilities as $unavailability) {
            $this->dispatch($event, $unavailability);
        }

        if (!empty($this->usersDispatched)) {
            $this->jobQueueInterface->aggregateUsersFullUnavailability($event, $this->usersDispatched);
        }
    }

    /**
     * Dispatch time slots of a mass unavailbilty between all participants of the event
     *
     * @throws UnableToDispatchException
     */
    private function dispatch(Event $event, Mass $mass)
    {
        if (!$mass->isDispatch()) {
            throw new UnableToDispatchException('Dispatch is not enabled on this mass unavailability.');
        }

        $timeSlots = $mass->getTimeSlots();

        if (empty($timeSlots)) {
            throw new UnableToDispatchException('No time slot available on this mass unavailability.');
        }

        $users = $this->userRepository->findByEventWithoutDispatch($mass->getEvent(), $mass);

        $isMassUnavailabilityAssignedToAllTypes = $this->isMassUnavailabilityAssignedToAllTypes->handle($event, $mass);

        foreach ($users as $index => $user) {
            if (!$isMassUnavailabilityAssignedToAllTypes) {
                $userTypes = $this->typeRepository->getTypesByUserIds($mass->getEvent(), [$user->getId()]);

                if (!$mass->hasAtLeastOneType($userTypes)) {
                    continue;
                }
            }

            $timeSlot = $timeSlots[$index % \count($timeSlots)];

            $assignment = new MassAssignment(
                $mass,
                $user,
                $timeSlot->getFrom(),
                $timeSlot->getTo()
            );

            $this->massAssignmentRepository->add($assignment);

            $this->usersDispatched[$user->getId()] = $user;
        }
    }
}
