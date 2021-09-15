<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipation;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipationHandler;
use Proximum\Vimeet\Domain\Happening\HappeningsNotOverlapped;
use Proximum\Vimeet\Domain\Happening\HappeningsWithProductsBySheetPackageGetter;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductUpdated;

class ParticipateToHappeningsByProductTest extends TestCase
{
    private $event;
    private $sheet;
    private $participant1;
    private $participant2;
    private $participant3;
    private $happening1;
    private $happening2;
    private $happening3;
    private $happening4;

    private $happeningsWithProductsBySheetPackageGetter;
    private $happeningsNotOverlapped;
    private $participateToHappeningWithProductToBuyChecker;
    private $updateParticipationHandler;

    private $participateToHappeningsByProduct;
    private $participantWithAttributedProductUpdated;

    public function setUp()
    {
        $this->happening1 = $this->prophesize(Happening::class);
        $this->happening1->getId()->willReturn(901);

        $this->happening2 = $this->prophesize(Happening::class);
        $this->happening2->getId()->willReturn(902);

        $this->happening3 = $this->prophesize(Happening::class);
        $this->happening3->getId()->willReturn(903);

        $this->happening4 = $this->prophesize(Happening::class);
        $this->happening4->getId()->willReturn(904);

        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant1->getId()->willReturn(111);

        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant2->getId()->willReturn(222);

        $this->participant3 = $this->prophesize(Participant::class);
        $this->participant3->getId()->willReturn(333);

        $this->participantWithAttributedProductUpdated = $this->prophesize(
            ParticipantWithAttributedProductUpdated::class
        );
        $this->happeningsWithProductsBySheetPackageGetter = $this->prophesize(HappeningsWithProductsBySheetPackageGetter::class);
        $this->happeningsNotOverlapped = $this->prophesize(HappeningsNotOverlapped::class);
        $this->participateToHappeningWithProductToBuyChecker = $this->prophesize(
            ParticipateToHappeningWithProductToBuyChecker::class
        );
        $this->updateParticipationHandler = $this->prophesize(UpdateParticipationHandler::class);
        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $this->participateToHappeningsByProduct = new ParticipateToHappeningsByProduct(
            $this->happeningsWithProductsBySheetPackageGetter->reveal(),
            $this->happeningsNotOverlapped->reveal(),
            $this->participantWithAttributedProductUpdated->reveal(),
            $this->participateToHappeningWithProductToBuyChecker->reveal(),
            $this->updateParticipationHandler->reveal(),
            $delayedEventDispatcher->reveal()
        );
    }

    public function testNoHappeningsWithProduct()
    {
        $this
            ->happeningsWithProductsBySheetPackageGetter
            ->get($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }

    public function testHandle()
    {
        $this
            ->happeningsWithProductsBySheetPackageGetter
            ->get($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    901 => $this->happening1->reveal(),
                    902 => $this->happening2->reveal(),
                    903 => $this->happening3->reveal(),
                ]
            )
        ;

        $this->sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ]
        );

        $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening1->reveal(), $this->happening2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$this->happening2->reveal()])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening2->reveal(), $this->happening3->reveal()])
            ->shouldBeCalled()
            ->willReturn([$this->happening2->reveal(), $this->happening3->reveal()])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening1->reveal(), $this->happening3->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening1->reveal(),
                    $this->sheet->reveal(),
                    []
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening2->reveal(),
                    $this->sheet->reveal(),
                    [
                        $this->participant1->reveal(),
                        $this->participant2->reveal(),
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening3->reveal(),
                    $this->sheet->reveal(),
                    [
                        $this->participant2->reveal(),
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }

    public function testWithFilteredParticipants()
    {
        $this
            ->happeningsWithProductsBySheetPackageGetter
            ->get($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    901 => $this->happening1->reveal(),
                ]
            )
        ;

        $this->sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ]
        );

        $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn([
                $this->participant1->reveal(),
            ])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening1->reveal(),
                    $this->sheet->reveal(),
                    []
                )
            )
            ->shouldBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }
}
