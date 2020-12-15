<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class PackageManager
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

    public function create(Event $event, string $title = 'Package'): Package
    {
        $package = new Package($event, $title, new \DateTime());
        $this->packageRepository->add($package);

        return $package;
    }

    public function assignProductParticipant(Package $package, Product $product)
    {
        if (!$product->isParticipant()) {
            throw new \InvalidArgumentException('The given product is not of type participant');
        }

        // As we set a product participant, we enable the participant step
        $package->enable(
            $package->isPlansEnabled(),
            true,
            $package->isOptionsEnabled()
        );

        $package->setParticipants(array_merge($package->getParticipants(), [$product]));

        $this->packageRepository->set($package);
    }

    public function assignPlan(Package $package, Product $product)
    {
        if (!$product->isPlan()) {
            throw new \InvalidArgumentException('The given product is not of type plan');
        }

        // As we set a product plan, we enable the plan step
        $package->enable(
            true,
            $package->isParticipantAndPlanningEnabled(),
            $package->isOptionsEnabled()
        );

        $package->setPlans(array_merge($package->getPlans(), [$product]));

        $this->packageRepository->set($package);
    }

    public function assignPlanning(Package $package, Product $planning)
    {
        if (!$planning->isPlanning()) {
            throw new \InvalidArgumentException('The given product is not of type plan');
        }

        // As we set a product planning, we enable the participant and planning step
        $package->enable(
            $package->isPlansEnabled(),
            true,
            $package->isOptionsEnabled()
        );

        $package->setPlanning($planning);

        $this->packageRepository->set($package);
    }

    /**
     * @param Product[] $options
     */
    public function setOptions(Package $package, array $options)
    {
        $package->setGroups(
            [
                $options,
            ],
            [
                [
                    'fr' => 'Groupe 1',
                    'en' => 'Group 1',
                ],
            ]
        );

        $this->packageRepository->set($package);
    }

    public function setPlanningNotSelectable(Package $package): void
    {
        $package->setPlanningSelectable(false);
        $this->packageRepository->set($package);
    }

    public function enable(Package $package): void
    {
        $package->enable(true, true, true);
        $this->packageRepository->set($package);
    }
}
