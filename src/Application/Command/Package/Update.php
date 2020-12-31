<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Command\Package\Model\Group;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;

class Update implements Command
{
    /** @var Package */
    public $package;

    /** @var string */
    public $title;

    /** @var Model\Plans */
    public $plans;

    /** @var Model\ParticipantAndPlanning */
    public $participantAndPlanning;

    /** @var Model\Options */
    public $options;

    public function __construct(Package $package)
    {
        $plansLabels = [];
        $participantAndPlanningLabels = [];
        $optionsLabels = [];

        foreach ($package->getEvent()->getLocales() as $locale) {
            $plansLabels[$locale]                  = $package->getPlansLabel($locale);
            $participantAndPlanningLabels[$locale] = $package->getParticipantAndPlanningLabel($locale);
            $optionsLabels[$locale]                = $package->getOptionsLabel($locale);
        }

        $this->package = $package;
        $this->title = $package->getTitle();
        $this->plans = new Model\Plans(
            $plansLabels,
            $package->isPlansEnabled(),
            $package->getPlans()
        );

        $this->participantAndPlanning = new Model\ParticipantAndPlanning(
            $participantAndPlanningLabels,
            $package->isParticipantAndPlanningEnabled(),
            $package->getMaxParticipant(),
            $package->getParticipants(),
            $package->getPlanning(),
            $package->isPlanningSelectable(),
            $package->isParticipantWithPlanning()
        );

        $this->options = new Model\Options(
            $optionsLabels,
            $package->isOptionsEnabled(),
            array_map(function (PackageGroup $group) {
                return new Group($group->getLabels(), $group->getOptions());
            }, $package->getGroups())
        );
    }
}
