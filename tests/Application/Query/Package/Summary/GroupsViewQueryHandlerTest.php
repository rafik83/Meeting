<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\ParticipantAndPlanningGroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ParticipantAndPlanningGroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\PlanGroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\PlanGroupViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\GroupsView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class GroupsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $package->enable(true, true, true);
        $sheet = SheetFactory::create($event, null, $dateTime, $type);

        $option      = ProductFactory::create($event, 'option');
        $participant = ProductFactory::create($event, 'participant');
        $planning    = ProductFactory::create($event, 'planning');

        $package->setPlanning($option);
        $type->setPackage($package);

        $cartRowOption      = new CartRow($sheet, $option, 1);
        $cartRowParticipant = new CartRow($sheet, $participant, 1);
        $cartRowPlanning    = new CartRow($sheet, $planning, 1);
        $cart               = new Cart($sheet, [$cartRowOption, $cartRowParticipant, $cartRowPlanning], []);

        $group = new PackageGroup($package, 1);
        $group->setOptions([$option]);
        $package->setGroupsModel([$group]);

        // Mock
        $planGroupViewQueryHandler        = $this->prophesize(PlanGroupViewQueryHandler::class);
        $participantAndPlanningGroupViewQueryHandler = $this->prophesize(ParticipantAndPlanningGroupViewQueryHandler::class);
        $groupViewQueryHandler            = $this->prophesize(GroupViewQueryHandler::class);

        $planGroupViewQueryHandler->handle(Argument::type(PlanGroupViewQuery::class))->shouldBeCalled();

        $participantAndPlanningGroupViewQueryHandler->handle(Argument::type(ParticipantAndPlanningGroupViewQuery::class))->shouldBeCalled();

        $groupViewQueryHandler->handle(Argument::type(GroupViewQuery::class))->shouldBeCalled();

        $handler = new GroupsViewQueryHandler(
            $planGroupViewQueryHandler->reveal(),
            $participantAndPlanningGroupViewQueryHandler->reveal(),
            $groupViewQueryHandler->reveal()
        );

        $query = new GroupsViewQuery($sheet, $cart, $locale);

        $this->assertInstanceOf(GroupsView::class, $handler->handle($query));
    }
}
