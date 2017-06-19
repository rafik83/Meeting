<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Composer;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingRequestViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $locale   = 'fr';
        $datetime = new \DateTime();
        $sheet            = $this->prophesize(Sheet::class);
        $sheet2           = $this->prophesize(Sheet::class);
        $user             = UserFactory::create();
        $meetingRequest   = $this->prophesize(Request::class);
        $preview          = $this->prophesize(Preview::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $ruleRepository   = $this->prophesize(RuleRepositoryInterface::class);
        $ruleComposer     = $this->prophesize(Composer::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $meetingRequest->getCreatedAt()->willReturn($datetime);
        $meetingRequest->getFromSheet()->willReturn($sheet->reveal());
        $meetingRequest->getToSheet()->willReturn($sheet2->reveal());
        $meetingRequest->getState()->willReturn(Request::STATE_SENT);
        $meetingRequest->hasMessage()->willReturn(true);
        $sheet->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type2->reveal());
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), $locale)->willReturn('sheet name');
        $ruleRepository->getBySeerTypeAndSeeableType($type1->reveal(), $type2->reveal())->shouldBeCalled()->willReturn([]);
        $preview->getPreview($sheet2->reveal(), $locale, null)->shouldBeCalled()->willReturn([]);

        $type2->getTitle($locale)->willReturn('type');

        $handler = new MeetingRequestViewQueryHandler(
            $preview->reveal(),
            $sheetInfoGuesser->reveal(),
            $ruleRepository->reveal(),
            $ruleComposer->reveal()
        );

        $result = $handler->handle(new MeetingRequestViewQuery(
            $meetingRequest->reveal(),
            $sheet->reveal(),
            $user,
            $locale,
            false,
            false,
            false,
            false
        ));

        $expected = new MeetingRequestView(
            $sheet2->reveal(),
            'sheet name',
            'sent',
            'type',
            $datetime,
            $meetingRequest->reveal(),
            [],
            false,
            false,
            false,
            false,
            false,
            true
        );

        $this->assertEquals($expected, $result);
    }
}
