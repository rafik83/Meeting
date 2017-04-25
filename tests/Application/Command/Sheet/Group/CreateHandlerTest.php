<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Sheet\Group;

use Prophecy\Promise\ThrowPromise;
use Proximum\Vimeet\Application\Command\Sheet\Group\Create;
use Proximum\Vimeet\Application\Command\Sheet\Group\CreateHandler;
use Proximum\Vimeet\Application\Exception\Group\NoSheetSelectedForGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
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

        $create        = new Create($event, $user, $sheetViews);
        $create->sheetViews['sheetViews'] = $sheetViews;
        $create->title = 'Groupe';
        $group         = new Group($event, $user, 'Groupe', $dateTime);

        $handler = new CreateHandler(
            $groupRepository->reveal(),
            $sheetRepository->reveal(),
            $dateTime
        );

        $sheetRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet);
        $groupRepository->add($group)->shouldBeCalled();

        $handler->handle($create);
    }
}
