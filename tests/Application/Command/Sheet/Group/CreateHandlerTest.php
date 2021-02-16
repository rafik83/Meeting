<?php

namespace Application\Command\Sheet\Group;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Group\Create;
use Proximum\Vimeet\Application\Command\Sheet\Group\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupCreatedEvent;
use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime   = new \DateTime();
        $event      = EventFactory::createEvent('Concerto en fa mineur de P.Sebastien');
        $user       = UserFactory::create('p.seb@elao.com');
        $sheet      = SheetFactory::create($event, $user, $dateTime);
        $sheetViews = [new SheetView(1, 'fiche 1')];

        $groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $create        = new Create($event, $user);
        $create->sheetViews = $sheetViews;
        $create->forceSheetTitle = true;
        $create->title = 'Groupe';
        $group         = new Group($event, $user, 'Groupe', true, $dateTime);

        $handler = new CreateHandler(
            $groupRepository->reveal(),
            $sheetRepository->reveal(),
            $dateTime,
            $eventDispatcher->reveal()
        );

        $sheetRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet);
        $groupRepository->add($group)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            new SheetGroupCreatedEvent($group)
        )->shouldBeCalled();

        $handler->handle($create);
    }
}
