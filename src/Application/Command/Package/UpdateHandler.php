<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class UpdateHandler
{
    /** @var PackageRepositoryInterface */
    private $packageRepository;

    /**
     * @param PackageRepositoryInterface $packageRepository
     */
    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update): void
    {
        $update->package
            ->setTitle($update->title)
            ->enable($update->plans->enabled, $update->participantAndPlanning->enabled, $update->options->enabled)
            ->setMaxParticipant($update->participantAndPlanning->maxParticipant)
            ->setPlans(array_values($update->plans->plans))
            ->setParticipants($update->participantAndPlanning->participants)
            ->setGroups($update->options->getGroupOptions(), $update->options->getGroupLabels())
        ;

        $update->package->setPlanning($update->participantAndPlanning->planning);
        $update->package->setPlanningSelectable($update->participantAndPlanning->planningSelectable);
        $update->package->setParticipantWithPlanning($update->participantAndPlanning->participantWithPlanning);

        foreach ($update->package->getEvent()->getLocales() as $locale) {
            $update->package->translate(
                $locale,
                $update->plans->getLabel($locale),
                $update->participantAndPlanning->getLabel($locale),
                $update->options->getLabel($locale)
            );
        }

        $this->packageRepository->set($update->package);
    }
}
