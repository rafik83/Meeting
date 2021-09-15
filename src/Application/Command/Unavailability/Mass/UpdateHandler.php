<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /** @var JobQueueInterface */
    private $jobQueueAdapter;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        MassRepositoryInterface $massRepository,
        JobQueueInterface $jobQueueAdapter,
        UserRepositoryInterface $userRepository,
        TypeRepositoryInterface $typeRepository,
        MassAssignmentRepositoryInterface $massAssignmentRepository
    ) {
        $this->massRepository = $massRepository;
        $this->jobQueueAdapter = $jobQueueAdapter;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->massAssignmentRepository = $massAssignmentRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->mass->update(
            $update->category,
            $update->name,
            $update->begin,
            $update->end,
            $update->blocking,
            $update->dispatch,
            $update->timeSlots,
            $update->types
        );

        foreach ($update->translations as $locale => $translation) {
            $update->mass->updateTranslation($locale, $translation['title'], $translation['description']);
        }

        $this->massRepository->update($update->mass);

        $usersDispatched = $this->userRepository->findByEventWithDispatch($update->mass->getEvent(), $update->mass);

        foreach ($usersDispatched as $user) {
            $userTypes = $this->typeRepository->getTypesByUserIds($update->mass->getEvent(), [$user->getId()]);

            if (!$update->mass->hasAtLeastOneType($userTypes)) {
                $this->massAssignmentRepository->removeByUserAndMass($user, $update->mass);
            }
        }

        $this->jobQueueAdapter->aggregateEventUsersFullUnavailability($update->mass->getEvent());
        $this->jobQueueAdapter->aggregateAvailableSlot($update->mass->getEvent());
    }
}
