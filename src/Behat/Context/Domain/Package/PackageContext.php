<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Package;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\PackageContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;

class PackageContext implements Context
{
    /** @var PackageContextProxyInterface */
    private $packageContextProxy;

    /**
     * @param PackageContextProxyInterface $packageContextProxy
     */
    public function __construct(PackageContextProxyInterface $packageContextProxy)
    {
        $this->packageContextProxy = $packageContextProxy;
    }

    /**
     * @Given /^there is a package "(?P<title>[^"]+)" for this event$/
     *
     * @param string $title
     */
    public function createInEvent(string $title)
    {
        $event = $this->packageContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $package = $this->packageContextProxy->getPackageManager()->create($event, $title);
        $this->packageContextProxy->getStorage()->set('package', $package);
    }

    /**
     * @Given /^this plan is assigned to this package$/
     */
    public function assignPlan()
    {
        $package = $this->packageContextProxy->getStorage()->get('package');
        $plan = $this->packageContextProxy->getStorage()->get('plan');

        if (null === $package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        if (null === $plan) {
            throw new \InvalidArgumentException('Missing Product Participant');
        }

        $this->packageContextProxy->getPackageManager()->assignPlan($package, $plan);
    }

    /**
     * @Given /^this product participant is assigned to this package$/
     */
    public function assignProductParticipant()
    {
        $package = $this->packageContextProxy->getStorage()->get('package');
        $productParticipant = $this->packageContextProxy->getStorage()->get('productParticipant');

        if (null === $package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        if (null === $productParticipant) {
            throw new \InvalidArgumentException('Missing Product Participant');
        }

        $this->packageContextProxy->getPackageManager()->assignProductParticipant($package, $productParticipant);
    }

    /**
     * @Given /^this product planning is assigned to this package$/
     */
    public function assignProductPlanning()
    {
        $package = $this->packageContextProxy->getStorage()->get('package');
        $planning = $this->packageContextProxy->getStorage()->get('productPlanning');

        if (null === $package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        if (null === $planning) {
            throw new \InvalidArgumentException('Missing Product Planning');
        }

        $this->packageContextProxy->getPackageManager()->assignPlanning($package, $planning);
    }

    /**
     * @Given /^these options are assigned to this package$/
     */
    public function theseOptionsAreAssignedToThisPackage()
    {
        $package = $this->packageContextProxy->getStorage()->get('package');

        if (!$package instanceof Package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        $options = $this->packageContextProxy->getStorage()->get('options');

        if (!\is_array($options)) {
            throw new \InvalidArgumentException('Missing options');
        }

        $this->packageContextProxy->getPackageManager()->setOptions($package, $options);
    }

    /**
     * @Given /^in this package, planning is not selectable$/
     */
    public function inThisPackagePlanningIsNotSelectable()
    {
        $package = $this->packageContextProxy->getStorage()->get('package');

        if (null === $package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        $this->packageContextProxy->getPackageManager()->setPlanningNotSelectable($package);
    }

    /**
     * @Given /^the package of this sheet is enabled$/
     */
    public function thePackageOfThisSheetIsEnabled()
    {
        /** @var Sheet */
        $sheet = $this->packageContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $this->packageContextProxy->getPackageManager()->enable($sheet->getPackage());
    }
}
