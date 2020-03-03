<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner\PrePlanningProcess;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformPriorityRequestsIntoMeeting;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformPriorityRequestsIntoMeetingHandler;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class TransformPriorityRequestsIntoMeetingHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $typeBuyer = $this->prophesize(Type::class);
        $typeLittleSeller = $this->prophesize(Type::class);
        $typeBigSeller = $this->prophesize(Type::class);
        $categorySeller = $this->prophesize(Category::class);
        $categorySeller->getTypes()
            ->willReturn([$typeBigSeller->reveal(), $typeLittleSeller->reveal()])
        ;

        // "bigger seller" see "buyer" : priority 1
        $ruleTypeBigSellerSeeTypeBuyer = $this->prophesize(Rule::class);
        $ruleTypeBigSellerSeeTypeBuyer->getSeer()
            ->willReturn($typeBigSeller->reveal())
        ;
        $ruleTypeBigSellerSeeTypeBuyer->getSeeable()
            ->willReturn($typeBuyer->reveal())
        ;
        $ruleTypeBigSellerSeeTypeBuyer->getPriority()
            ->willReturn(1)
        ;

        // "little seller" see "buyer" : priority 2
        $ruleTypeLittleSellerSeeTypeBuyer = $this->prophesize(Rule::class);
        $ruleTypeLittleSellerSeeTypeBuyer->getSeer()
            ->willReturn($typeLittleSeller->reveal())
        ;
        $ruleTypeLittleSellerSeeTypeBuyer->getSeeable()
            ->willReturn($typeBuyer->reveal())
        ;
        $ruleTypeLittleSellerSeeTypeBuyer->getPriority()
            ->willReturn(2)
        ;

        // "buyer" see "seller" : priority 3
        $ruleTypeBuyerSeeCatSeller = $this->prophesize(Rule::class);
        $ruleTypeBuyerSeeCatSeller->getSeer()
            ->willReturn($typeBuyer->reveal())
        ;
        $ruleTypeBuyerSeeCatSeller->getSeeable()
            ->willReturn($categorySeller->reveal())
        ;
        $ruleTypeBuyerSeeCatSeller->getPriority()
            ->willReturn(3)
        ;

        // sheets
        $bigSellerCarrefour = $this->prophesize(Sheet::class);
        $bigSellerCarrefour->getType()->willReturn($typeBigSeller->reveal());
        $bigSellerCarrefour->getId()->willReturn(123);

        $bigSellerCasino = $this->prophesize(Sheet::class);
        $bigSellerCasino->getType()->willReturn($typeBigSeller->reveal());
        $bigSellerCasino->getId()->willReturn(321);

        $littleSellerCoccimarket = $this->prophesize(Sheet::class);
        $littleSellerCoccimarket->getType()->willReturn($typeLittleSeller->reveal());
        $littleSellerCoccimarket->getId()->willReturn(741);

        $littleSellerCarrefourExpress = $this->prophesize(Sheet::class);
        $littleSellerCarrefourExpress->getType()->willReturn($typeLittleSeller->reveal());
        $littleSellerCarrefourExpress->getId()->willReturn(147);

        $buyerGaston = $this->prophesize(Sheet::class);
        $buyerGaston->getType()->willReturn($typeBuyer->reveal());
        $buyerGaston->getId()->willReturn(963);

        $buyerLuckyLuke = $this->prophesize(Sheet::class);
        $buyerLuckyLuke->getType()->willReturn($typeBuyer->reveal());
        $buyerLuckyLuke->getId()->willReturn(369);

        $buyerIsabelle = $this->prophesize(Sheet::class);
        $buyerIsabelle->getType()->willReturn($typeBuyer->reveal());
        $buyerIsabelle->getId()->willReturn(789);

        $buyerMichu = $this->prophesize(Sheet::class);
        $buyerMichu->getType()->willReturn($typeBuyer->reveal());
        $buyerMichu->getId()->willReturn(987);

        // requests
        //    weight : 1 0 001
        $requestCarrefourToGaston = $this->prophesize(Request::class);
        $requestCarrefourToGaston->getEvent()->willReturn($event->reveal());
        $requestCarrefourToGaston->getFromSheet()->willReturn($bigSellerCarrefour->reveal());
        $requestCarrefourToGaston->getToSheet()->willReturn($buyerGaston->reveal());
        $requestCarrefourToGaston->isFromPriority()->willReturn(true);
        $requestCarrefourToGaston->isToPriority()->willReturn(true);
        $requestCarrefourToGaston->getId()->willReturn('CarrefourToGaston'); // for debugging purpose

        //    weight : 1 0 001
        $requestCasinoToGaston = $this->prophesize(Request::class);
        $requestCasinoToGaston->getEvent()->willReturn($event->reveal());
        $requestCasinoToGaston->getFromSheet()->willReturn($bigSellerCasino->reveal());
        $requestCasinoToGaston->getToSheet()->willReturn($buyerGaston->reveal());
        $requestCasinoToGaston->isFromPriority()->willReturn(true);
        $requestCasinoToGaston->isToPriority()->willReturn(true);
        $requestCasinoToGaston->getId()->willReturn('CasinoToGaston'); // for debugging purpose

        //    weight : 1 0 002
        $requestCasinoToLuckyLuke = $this->prophesize(Request::class);
        $requestCasinoToLuckyLuke->getEvent()->willReturn($event->reveal());
        $requestCasinoToLuckyLuke->getFromSheet()->willReturn($bigSellerCasino->reveal());
        $requestCasinoToLuckyLuke->getToSheet()->willReturn($buyerGaston->reveal());
        $requestCasinoToLuckyLuke->isFromPriority()->willReturn(true);
        $requestCasinoToLuckyLuke->isToPriority()->willReturn(true);
        $requestCasinoToLuckyLuke->getId()->willReturn('CasinoToLuckyLuke'); // for debugging purpose

        //    weight : 1 1 001
        $requestCarrefourToIsabelle = $this->prophesize(Request::class);
        $requestCarrefourToIsabelle->getEvent()->willReturn($event->reveal());
        $requestCarrefourToIsabelle->getFromSheet()->willReturn($bigSellerCarrefour->reveal());
        $requestCarrefourToIsabelle->getToSheet()->willReturn($buyerIsabelle->reveal());
        $requestCarrefourToIsabelle->isFromPriority()->willReturn(false);
        $requestCarrefourToIsabelle->isToPriority()->willReturn(true);
        $requestCarrefourToIsabelle->getId()->willReturn('CarrefourToIsabelle'); // for debugging purpose

        //    weight : 2 0 001
        $requestCoccimarketToGaston = $this->prophesize(Request::class);
        $requestCoccimarketToGaston->getEvent()->willReturn($event->reveal());
        $requestCoccimarketToGaston->getFromSheet()->willReturn($littleSellerCoccimarket->reveal());
        $requestCoccimarketToGaston->getToSheet()->willReturn($buyerGaston->reveal());
        $requestCoccimarketToGaston->isFromPriority()->willReturn(true);
        $requestCoccimarketToGaston->isToPriority()->willReturn(true);
        $requestCoccimarketToGaston->getId()->willReturn('CoccimarketToGaston'); // for debugging purpose

        //    weight : 2 1 001
        $requestCarrefourExpressToIsabelle = $this->prophesize(Request::class);
        $requestCarrefourExpressToIsabelle->getEvent()->willReturn($event->reveal());
        $requestCarrefourExpressToIsabelle->getFromSheet()->willReturn($littleSellerCarrefourExpress->reveal());
        $requestCarrefourExpressToIsabelle->getToSheet()->willReturn($buyerIsabelle->reveal());
        $requestCarrefourExpressToIsabelle->isFromPriority()->willReturn(true);
        $requestCarrefourExpressToIsabelle->isToPriority()->willReturn(false);
        $requestCarrefourExpressToIsabelle->getId()->willReturn('CarrefourExpressToIsabelle'); // for debugging purpose

        //    weight : 2 1 002
        $requestCoccimarketToIsabelle = $this->prophesize(Request::class);
        $requestCoccimarketToIsabelle->getEvent()->willReturn($event->reveal());
        $requestCoccimarketToIsabelle->getFromSheet()->willReturn($littleSellerCoccimarket->reveal());
        $requestCoccimarketToIsabelle->getToSheet()->willReturn($buyerIsabelle->reveal());
        $requestCoccimarketToIsabelle->isFromPriority()->willReturn(true);
        $requestCoccimarketToIsabelle->isToPriority()->willReturn(false);
        $requestCoccimarketToIsabelle->getId()->willReturn('CoccimarketToIsabelle'); // for debugging purpose

        //    weight : 2 1 003
        $requestCoccimarketToLuckyLuke = $this->prophesize(Request::class);
        $requestCoccimarketToLuckyLuke->getEvent()->willReturn($event->reveal());
        $requestCoccimarketToLuckyLuke->getFromSheet()->willReturn($littleSellerCoccimarket->reveal());
        $requestCoccimarketToLuckyLuke->getToSheet()->willReturn($buyerLuckyLuke->reveal());
        $requestCoccimarketToLuckyLuke->isFromPriority()->willReturn(true);
        $requestCoccimarketToLuckyLuke->isToPriority()->willReturn(false);
        $requestCoccimarketToLuckyLuke->getId()->willReturn('CoccimarketToLuckyLuke'); // for debugging purpose

        //    weight : 3 1 001
        $requestMichuToCarrefour = $this->prophesize(Request::class);
        $requestMichuToCarrefour->getEvent()->willReturn($event->reveal());
        $requestMichuToCarrefour->getFromSheet()->willReturn($buyerMichu->reveal());
        $requestMichuToCarrefour->getToSheet()->willReturn($bigSellerCarrefour->reveal());
        $requestMichuToCarrefour->isFromPriority()->willReturn(true);
        $requestMichuToCarrefour->isToPriority()->willReturn(false);
        $requestMichuToCarrefour->getId()->willReturn('MichuToCarrefour'); // for debugging purpose

        $meetingCarrefourToGaston = $this->prophesize(Meeting::class);
        $meetingCasinoToGaston = $this->prophesize(Meeting::class);
        $meetingCasinoToLuckyLuke = $this->prophesize(Meeting::class);
        $meetingCarrefourToIsabelle = $this->prophesize(Meeting::class);
        $meetingCoccimarketToGaston = $this->prophesize(Meeting::class);
        $meetingCarrefourExpressToIsabelle = $this->prophesize(Meeting::class);
        $meetingCoccimarketToIsabelle = $this->prophesize(Meeting::class);
        $meetingCoccimarketToLuckyLuke = $this->prophesize(Meeting::class);
        $meetingMichuToCarrefour = $this->prophesize(Meeting::class);

        // prophecy dependencies
        $commandBus = $this->prophesize(CommandBusInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);

        // warning : requests order can't be fully random because algorithm is order sensitive
        $allRequests = [
            $requestMichuToCarrefour->reveal(), // 3 1 001 (final index : 8)
            $requestCarrefourToGaston->reveal(), // 1 0 001 (final index : 0)
            $requestCarrefourExpressToIsabelle->reveal(), // 2 1 001 (final index : 5)
            $requestCoccimarketToIsabelle->reveal(), // 2 1 002 (final index : 6)
            $requestCarrefourToIsabelle->reveal(), // 1 1 001 (final index : 3)
            $requestCasinoToGaston->reveal(), // 1 0 001 (final index : 1)
            $requestCoccimarketToGaston->reveal(), // 2 0 001 (final index : 4)
            $requestCoccimarketToLuckyLuke->reveal(), // 2 1 003 (final index : 7)
            $requestCasinoToLuckyLuke->reveal(), // 1 0 002 (final index : 2)
        ];

        $requestRepository->findApprovedAndPrioritizedWithoutMeeting($event->reveal())
            ->shouldBeCalled()
            ->willReturn($allRequests)
        ;

        $ruleRepository->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [$ruleTypeBuyerSeeCatSeller, $ruleTypeLittleSellerSeeTypeBuyer, $ruleTypeBigSellerSeeTypeBuyer]
            )
        ;

        //      generated meetings
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCarrefourToGaston->reveal(), Meeting::CREATED_BY_PLANNER, true, true
            )
        )
            ->willReturn($meetingCarrefourToGaston->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting($requestCasinoToGaston->reveal(), Meeting::CREATED_BY_PLANNER, true, true)
        )
            ->willReturn($meetingCasinoToGaston->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCasinoToLuckyLuke->reveal(), Meeting::CREATED_BY_PLANNER, true, true
            )
        )
            ->willReturn($meetingCasinoToLuckyLuke->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCarrefourToIsabelle->reveal(),
                Meeting::CREATED_BY_PLANNER,
                true,
                true
            )
        )
            ->willReturn($meetingCarrefourToIsabelle->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCoccimarketToGaston->reveal(),
                Meeting::CREATED_BY_PLANNER,
                true,
                true
            )
        )
            ->willReturn($meetingCoccimarketToGaston->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCarrefourExpressToIsabelle->reveal(),
                Meeting::CREATED_BY_PLANNER,
                true,
                true
            )
        )
            ->willReturn($meetingCarrefourExpressToIsabelle->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCoccimarketToIsabelle->reveal(),
                Meeting::CREATED_BY_PLANNER,
                true,
                true
            )
        )
            ->willReturn($meetingCoccimarketToIsabelle->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting(
                $requestCoccimarketToLuckyLuke->reveal(),
                Meeting::CREATED_BY_PLANNER,
                true,
                true
            )
        )
            ->willReturn($meetingCoccimarketToLuckyLuke->reveal())
        ;
        $commandBus->handle(
            new TransformRequestIntoMeeting($requestMichuToCarrefour->reveal(), Meeting::CREATED_BY_PLANNER, true, true)
        )
            ->willReturn($meetingMichuToCarrefour->reveal())
        ;

        // run tests
        $command = new TransformPriorityRequestsIntoMeeting(
            $event->reveal(),
            ExportSolutionType::SOLUTION_OPTIMIZE_LOCKED
        );
        $handler = new TransformPriorityRequestsIntoMeetingHandler(
            $commandBus->reveal(),
            $requestRepository->reveal(),
            $ruleRepository->reveal()
        );
        $result = $handler->handle($command);

        $expected = [
            $meetingCarrefourToGaston->reveal(), // 1 0 001
            $meetingCasinoToGaston->reveal(), // 1 0 001
            $meetingCasinoToLuckyLuke->reveal(), // 1 0 002
            $meetingCarrefourToIsabelle->reveal(), // 1 1 001
            $meetingCoccimarketToGaston->reveal(), // 2 0 001
            $meetingCarrefourExpressToIsabelle->reveal(), // 2 1 001
            $meetingCoccimarketToIsabelle->reveal(), // 2 1 002
            $meetingCoccimarketToLuckyLuke->reveal(), // 2 1 003
            $meetingMichuToCarrefour->reveal(), // 3 1 001
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_no_requests_with_priority(): void
    {
        $event = $this->prophesize(Event::class);

        $commandBus = $this->prophesize(CommandBusInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);

        $requestRepository->findApprovedAndPrioritizedWithoutMeeting($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $command = new TransformPriorityRequestsIntoMeeting(
            $event->reveal(),
            ExportSolutionType::SOLUTION_OPTIMIZE_LOCKED
        );
        $handler = new TransformPriorityRequestsIntoMeetingHandler(
            $commandBus->reveal(),
            $requestRepository->reveal(),
            $ruleRepository->reveal()
        );

        $this->assertEquals([], $handler->handle($command));
    }
}
