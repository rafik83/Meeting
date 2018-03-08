<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale      = 'fr';
        $sheet       = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $user        = $this->prophesize(User::class);
        $group       = $this->prophesize(Sheet\Group::class);
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $sheet->getId()->willReturn(12345);
        $sheet->getTitle()->willReturn('Sheet Title');
        $sheet->getType()->willReturn($type);
        $sheet->getGroup()->willReturn($group->reveal());
        $participant->getId()->willReturn(1);
        $group->getId()->willReturn(2);

        $sheet->getUserParticipant($user)->willReturn($participant->reveal());

        $validationRequiredChecker = $this->prophesize(ValidationRequiredChecker::class);
        $router                    = $this->prophesize(RouterInterface::class);

        $validationRequiredChecker->handle(
            $sheet,
            $user
        )->shouldBeCalled()->willReturn(true);

        $router->generate('event_user_phone_redirect_to_validation', [
            'sheet'       => 12345,
            'participant' => 1,
            'redirectTo'  => 'redirectToLink',
        ])->shouldBeCalled()->willReturn('validationPhoneLink');

        $router->generate('event_sheet_group_requests_list', [
            'sheetGroup' => 2,
        ])->shouldBeCalled()->willReturn('redirectToLink');

        $handler = new SheetViewQueryHandler(
            $validationRequiredChecker->reveal(),
            $router->reveal()
        );
        $result  = $handler->handle(new SheetViewQuery($sheet->reveal(), $user->reveal(), $locale));

        $expected = new SheetView(12345, 'Sheet Title', $sheet->reveal(), '', true, 'validationPhoneLink');

        $this->assertEquals($expected, $result);
    }
}
