<?php

namespace Proximum\Vimeet\Tests\Application\Command\Package;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Package\Model\Group;
use Proximum\Vimeet\Application\Command\Package\Update;
use Proximum\Vimeet\Application\Command\Package\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);

        $dateTime = new \DateTimeImmutable();
        $event    = EventFactory::createEvent();
        $event->setLocales(['fr', 'en']);
        $package  = new Package($event, 'Lorem ipsum', $dateTime);

        $plan1       = Product::createPlan($event, 'Plan 1', 'plan1.jpg', 100, 20, 1, 1);
        $plan2       = Product::createPlan($event, 'Plan 2', 'plan2.jpg', 400, 20, 1, 1);
        $plan3       = Product::createPlan($event, 'Plan 3', 'plan3.jpg', 800, 20, 1, 1);
        $participant = Product::createParticipant($event, 'Participant', 300, 20, 5);
        $planning    = Product::createPlanning($event, 'Planning', 200, 20, 5);
        $option1     = Product::createOption($event, 'A', 'a.jpg', 250, 20, 4, 1, 4, true, $dateTime);
        $option2     = Product::createOption($event, 'B', 'b.jpg', 300, 20, 2, 10, 10, true, $dateTime);
        $option3     = Product::createOption($event, 'C', 'c.jpg', 500, 20, 10, 5, 9, true, $dateTime);
        $option4     = Product::createOption($event, 'D', 'd.jpg', 999, 20, 1, 1, 1, true, $dateTime);

        $expected = new Package($event, 'Foobar', $dateTime);
        $expected->translate('fr', 'Plans fr', 'P&P fr', 'Options fr');
        $expected->translate('en', 'Plans en', 'P&P en', 'Options en');
        $expected->setPlans([$plan1, $plan3, $plan2]);
        $expected->setParticipants([$participant]);
        $expected->setPlanning($planning);
        $expected->setMaxParticipant(12);
        $expected->setGroups([
            [$option4, $option1],
            [$option2, $option3],
        ], [
            ['fr' => 'AAAA', 'en' => 'AAAA'],
            ['fr' => 'BBBB', 'en' => 'BBBB'],
        ]);
        $expected->setPlanningSelectable(true);
        $expected->setParticipantWithPlanning(true);

        $packageRepository->set(Argument::that(function (Package $package) use ($expected) {
            $this->assertEquals($expected->getTitle(), $package->getTitle());
            $this->assertEquals($expected->getPlansLabel('fr'), $package->getPlansLabel('fr'));
            $this->assertEquals($expected->getPlansLabel('en'), $package->getPlansLabel('en'));
            $this->assertEquals($expected->getParticipantAndPlanningLabel('fr'), $package->getParticipantAndPlanningLabel('fr'));
            $this->assertEquals($expected->getParticipantAndPlanningLabel('en'), $package->getParticipantAndPlanningLabel('en'));
            $this->assertEquals($expected->getOptionsLabel('fr'), $package->getOptionsLabel('fr'));
            $this->assertEquals($expected->getOptionsLabel('en'), $package->getOptionsLabel('en'));
            $this->assertEquals($expected->getPlans(), $package->getPlans());
            $this->assertEquals($expected->getGroups(), $package->getGroups());
            $this->assertEquals($expected->getMaxParticipant(), $package->getMaxParticipant());
            $this->assertEquals($expected->isPlanningSelectable(), $package->isPlanningSelectable());
            $this->assertEquals($expected->isParticipantWithPlanning(), $package->isParticipantWithPlanning());

            $groups = $expected->getGroups();

            foreach ($package->getGroups() as $rank => $group) {
                $this->assertEquals($groups[$rank], $group);
            }

            $this->assertEquals($expected->isPlansEnabled(), $package->isPlansEnabled());
            $this->assertEquals($expected->isParticipantAndPlanningEnabled(), $package->isParticipantAndPlanningEnabled());
            $this->assertEquals($expected->isOptionsEnabled(), $package->isOptionsEnabled());
            $this->assertEquals($expected->getParticipants(), $package->getParticipants());
            $this->assertEquals($expected->getPlanning(), $package->getPlanning());

            return true;
        }))->shouldBeCalled();

        $command = new Update($package);
        $command->title = 'Foobar';
        $command->plans->labels = ['fr' => 'Plans fr', 'en' => 'Plans en'];
        $command->participantAndPlanning->labels = ['fr' => 'P&P fr', 'en' => 'P&P en'];
        $command->options->labels = ['fr' => 'Options fr', 'en' => 'Options en'];
        $command->plans->plans = [$plan1, $plan3, $plan2];
        $command->participantAndPlanning->participants = [$participant];
        $command->participantAndPlanning->planning = $planning;
        $command->participantAndPlanning->maxParticipant = 12;
        $command->options->groups = [
            new Group(['fr' => 'AAAA', 'en' => 'AAAA'], [$option4, $option1]),
            new Group(['fr' => 'BBBB', 'en' => 'BBBB'], [$option2, $option3]),
        ];
        $command->participantAndPlanning->planningSelectable = true;
        $command->participantAndPlanning->participantWithPlanning = true;

        $handler = new UpdateHandler($packageRepository->reveal());
        $handler->handle($command);
    }
}
