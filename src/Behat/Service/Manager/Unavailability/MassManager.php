<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class MassManager
{
    /** @var MassRepositoryInterface */
    private $massRepository;

    /** @var MassAssignmentRepositoryInterface */
    private $massAssignmentRepository;

    /** @var CategoryManager */
    private $categoryManager;

    public function __construct(
        MassRepositoryInterface $massRepository,
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        CategoryManager $categoryManager
    ) {
        $this->massRepository = $massRepository;
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->categoryManager = $categoryManager;
    }

    public function create(Event $event, ?Category $category, string $name, \DateTime $begin, \DateTimeInterface $end, Type $type): Mass
    {
        if (null === $category) {
            $category = $this->categoryManager->create($event, 'Mass unavailabilty default category');
        }

        $mass = new Mass(
            $event,
            $category,
            $name,
            $begin,
            $end,
            true,
            true,
            [],
            [$type]
        );
        $mass->createTranslation($event->getLocaleFallback(), $name, $name.' description');

        $this->massRepository->create($mass);

        return $mass;
    }

    public function assignSlotToMass(MeetingSlot $slot, User $user, Mass $mass)
    {
        $massAssignment = new MassAssignment($mass, $user, $slot->getBegin(), $slot->getEnd());
        $this->massAssignmentRepository->add($massAssignment);
    }
}
