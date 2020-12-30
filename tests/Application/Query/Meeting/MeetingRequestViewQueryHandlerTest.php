<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Composer;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingRequestViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale           = 'fr';
        $datetime         = new \DateTime();
        $sheet            = $this->prophesize(Sheet::class);
        $sheet2           = $this->prophesize(Sheet::class);
        $user             = UserFactory::create();
        $participant      = $this->prophesize(Participant::class);
        $meetingRequest   = $this->prophesize(Request::class);
        $preview          = $this->prophesize(Preview::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $ruleRepository   = $this->prophesize(RuleRepositoryInterface::class);
        $ruleComposer     = $this->prophesize(Composer::class);
        $router           = $this->prophesize(RouterInterface::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $meetingRequest->getCreatedAt()->willReturn($datetime);
        $meetingRequest->getFromSheet()->willReturn($sheet->reveal());
        $meetingRequest->getToSheet()->willReturn($sheet2->reveal());
        $meetingRequest->getState()->willReturn(Request::STATE_SENT);
        $meetingRequest->hasMessage()->willReturn(true);
        $meetingRequest->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheet2->reveal());
        $participant->getId()->willReturn(1);
        $sheet->getId()->willReturn(1);
        $sheet->getType()->willReturn($type1->reveal());
        $sheet->getUserParticipant($user)->willReturn($participant->reveal());
        $sheet2->getType()->willReturn($type2->reveal());
        $sheet2->getId()->willReturn(1337);
        $sheet2->getCategoriesTitles('fr')->shouldBeCalled()->willReturn('category');
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), $locale)->willReturn('sheet name');
        $ruleRepository->getBySeerSheetAndSeeableSheet($sheet->reveal(), $sheet2->reveal())->shouldBeCalled()
            ->willReturn([]);
        $preview->getPreview($sheet2->reveal(), $locale, null)->shouldBeCalled()->willReturn([]);

        $router->generate('event_user_phone_redirect_to_validation', [
            'sheet'       => 1,
            'participant' => 1,
            'redirectTo' => 'redirectLink/from/1/to/1337',
        ])->shouldBeCalled()->willReturn('validatePhoneLink');

        $router
            ->generate(
                'event_catalog_complete_sheet',
                [
                    'sheet'          => 1,
                    'sheetToDisplay' => 1337,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('redirectLink/from/1/to/1337')
        ;

        $handler = new MeetingRequestViewQueryHandler(
            $preview->reveal(),
            $sheetInfoGuesser->reveal(),
            $ruleRepository->reveal(),
            $ruleComposer->reveal(),
            $router->reveal()
        );

        $result = $handler->handle(new MeetingRequestViewQuery(
            $meetingRequest->reveal(),
            $sheet->reveal(),
            $user,
            $locale,
            false,
            false,
            false,
            false,
            false,
            true,
            true
        ));

        $expected = new MeetingRequestView(
            $sheet2->reveal(),
            'sheet name',
            'sent',
            'category',
            $datetime,
            $meetingRequest->reveal(),
            [],
            false,
            false,
            false,
            false,
            true,
            false,
            true,
            'validatePhoneLink'
        );

        $this->assertEquals($expected, $result);
    }
}
