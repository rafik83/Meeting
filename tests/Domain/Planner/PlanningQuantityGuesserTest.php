<?php

namespace Proximum\Vimeet\Tests\Domain\Planner;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Planner\PlanningQuantityGuesser;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class PlanningQuantityGuesserTest extends TestCase
{
    /**
     * @var ObjectProphecy of Order Merger
     */
    private $orderMerger;

    /**
     * @var ObjectProphecy Prophecy of OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     *  Initialize the prophecies
     */
    public function setUp()
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->orderMerger     = $this->prophesize(Merger::class);
    }

    public function testGuessWithNoPackage()
    {
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $package = new Package($event, 'title', new \DateTime());
        $sheet->getType()->setPackage($package);
        $package->enable(false, false, false);

        $planningQuantityGuesser = new PlanningQuantityGuesser(
            $this->orderRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $this->assertEquals(0, $planningQuantityGuesser->guess($sheet));
    }

    public function testGuessWithNoPackageAndParticipant()
    {
        $event        = EventFactory::createEvent();
        $sheet        = SheetFactory::create($event);
        // Useful as this method add the participant on the sheet
        $participant  = ParticipantFactory::create($sheet);
        $participant2 = ParticipantFactory::create($sheet);
        $package = new Package($event, 'title', new \DateTime());
        $package->enable(false, false, false);
        $package->setParticipantWithPlanning(false);
        $sheet->getType()->setPackage($package);

        $planningQuantityGuesser = new PlanningQuantityGuesser(
            $this->orderRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $this->assertEquals(2, $planningQuantityGuesser->guess($sheet));
    }

    public function testGuessWithPackageAndOptionParticipantWithPlanningActivated()
    {
        $event        = EventFactory::createEvent();
        $sheet        = SheetFactory::create($event);
        // Useful as this method add the participant on the sheet
        $participant  = ParticipantFactory::create($sheet);
        $participant2 = ParticipantFactory::create($sheet);
        $package = new Package($event, 'title', new \DateTime());
        $package->enable(true, true, false);
        $package->setParticipantWithPlanning(true);
        $sheet->getType()->setPackage($package);

        $planningQuantityGuesser = new PlanningQuantityGuesser(
            $this->orderRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $this->assertEquals(2, $planningQuantityGuesser->guess($sheet));
    }

    public function testGuessWithPackageAndNoOrder()
    {
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $package = new Package($event, 'title', new \DateTime());
        $sheet->getType()->setPackage($package);

        $this->orderRepository->findNotCancelledBySheet($sheet)->shouldBeCalled()->willReturn([]);

        $planningQuantityGuesser = new PlanningQuantityGuesser(
            $this->orderRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $this->assertEquals(0, $planningQuantityGuesser->guess($sheet));
    }

    public function testGuessWithPackageAndOrder()
    {
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $package = new Package($event, 'title', new \DateTime());
        $sheet->getType()->setPackage($package);

        $order = new Order($sheet, '', new \DateTime());
        $product = Product::createPlanning($event, 'name', 100, 20, 10);
        $plan    = Product::createPlan($event, 'plan', '', 200, 20, 20, 50);
        $plan->includeProduct($product, 1);
        $order->addRow(new Order\Row($order, 2, 20, $product));
        $order->addRow(new Order\Row($order, 1, 20, $plan));

        $this->orderRepository->findNotCancelledBySheet($sheet)->shouldBeCalled()->willReturn([$order]);
        $this->orderMerger->merge([$order])->shouldBeCalled()->willReturn($order);

        $planningQuantityGuesser = new PlanningQuantityGuesser(
            $this->orderRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $this->assertEquals(3, $planningQuantityGuesser->guess($sheet));
    }
}
