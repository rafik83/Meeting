<?php

namespace Proximum\Vimeet\Domain\Package;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;

class Duplicator
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * This method duplicates a package with an array of corresponding product
     * To duplicate a package from an event to another one
     *
     * @param Event   $toEvent
     * @param Package $fromPackage
     * @param array   $correspondingProducts
     *
     * @return Package
     */
    public function duplicatePackageWithCorrespondingProducts(
        Event $toEvent,
        Package $fromPackage,
        array $correspondingProducts
    ): Package {
        $toPackage = new Package($toEvent, $fromPackage->getTitle(), $this->dateTime);

        $locales = $toEvent->getLocales();

        foreach ($locales as $locale) {
            $toPackage->translate(
                $locale,
                $fromPackage->getPlansLabel($locale),
                $fromPackage->getParticipantAndPlanningLabel($locale),
                $fromPackage->getOptionsLabel($locale)
            );
        }

        $plans = [];
        foreach ($fromPackage->getPlans() as $plan) {
            $plans[] = $correspondingProducts[$plan->getId()];
        }
        $toPackage->setPlans($plans);

        $productsParticipant = [];

        foreach ($fromPackage->getParticipants() as $productParticipant) {
            if (isset($correspondingProducts[$productParticipant->getId()])) {
                $productsParticipant[] = $correspondingProducts[$productParticipant->getId()];
            }
        }

        $toPackage->setParticipants($productsParticipant);

        if (null !== $fromPackage->getPlanning()) {
            $toPackage->setPlanning($correspondingProducts[$fromPackage->getPlanning()->getId()]);
        }

        $groups = [];
        foreach ($fromPackage->getGroups() as $fromGroup) {
            $toGroup = new PackageGroup($toPackage, $fromGroup->getRank());

            foreach ($locales as $locale) {
                $toGroup->translate($locale, $fromGroup->getLabel($locale));
            }

            $options = [];
            foreach ($fromGroup->getOptions() as $option) {
                $options[] = $correspondingProducts[$option->getId()];
            }
            $toGroup->setOptions($options);

            $groups[] = $toGroup;
        }

        $toPackage->setGroupsModel($groups);
        $toPackage->setPlanningSelectable($fromPackage->isPlanningSelectable());
        $toPackage->setParticipantWithPlanning($fromPackage->isParticipantWithPlanning());

        $toPackage->enable(
            $fromPackage->isPlansEnabled(),
            $fromPackage->isParticipantAndPlanningEnabled(),
            $fromPackage->isOptionsEnabled()
        );

        return $toPackage;
    }

    /**
     * @param Package $fromPackage
     * @param string  $title
     *
     * @return Package
     */
    public function duplicatePackage(Package $fromPackage, string $title): Package
    {
        $event     = $fromPackage->getEvent();
        $toPackage = new Package($event, $title, $this->dateTime);

        // handle translations
        foreach ($event->getLocales() as $locale) {
            $toPackage->translate(
                $locale,
                $fromPackage->getPlansLabel($locale),
                $fromPackage->getParticipantAndPlanningLabel($locale),
                $fromPackage->getOptionsLabel($locale)
            );
        }

        // handle package group
        $groupOptions = [];
        $groupLabels  = [];

        /** @var PackageGroup $group */
        foreach ($fromPackage->getGroups() as $group) {
            $groupOptions[] = $group->getOptions();
            $groupLabels[]  = $group->getLabels();
        }

        $toPackage->setGroups($groupOptions, $groupLabels);

        // handle package plans
        $toPackage->setPlans($fromPackage->getPlans());

        // handle package participant
        $toPackage->setParticipants($fromPackage->getParticipants());

        $toPackage->setPlanningSelectable($fromPackage->isPlanningSelectable());
        $toPackage->setParticipantWithPlanning($fromPackage->isParticipantWithPlanning());

        // handle package planning
        if (null !== $fromPackage->getPlanning()) {
            $toPackage->setPlanning($fromPackage->getPlanning());
        }

        return $toPackage;
    }
}
