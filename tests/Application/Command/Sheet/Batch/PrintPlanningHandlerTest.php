<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Batch;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\Batch\PrintPlanning;
use Proximum\Vimeet\Application\Command\Sheet\Batch\PrintPlanningHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class PrintPlanningHandlerTest extends TestCase
{
    public function testHandlePlanning(): void
    {
        $event = $this->prophesize(Event::class);
        $sheetIds = [11, 12, 13, 14, 15, 16];
        $admin = $this->prophesize(Admin::class);
        $admin->getEmail()->shouldBeCalled()->willReturn('email@example.net');
        $orderBy = PlanningOrderedBy::ORDER_BY_SHEET_TITLE;
        $admin->getLocale()->willReturn('fr');

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $dateTime = new \DateTime();

        $extraData = new Event\ExtraData($event->reveal(), Type::ADMIN_SHEET_BATCH_IDS, '11,12,13,14,15,16', $dateTime);
        $extraDataRepository->add($extraData)->shouldBeCalled();

        $jobQueue
            ->printPlanning($extraData, $orderBy, 'email@example.net', 'fr', 'printPlanning')
            ->shouldBeCalled()
        ;

        $handler = new PrintPlanningHandler(
            $jobQueue->reveal(),
            $extraDataRepository->reveal(),
            $dateTime
        );

        $command = new PrintPlanning($event->reveal(), $sheetIds, $admin->reveal(), $orderBy, 'fr', 'printPlanning');
        $result = $handler->handle($command);

        $expected = new BatchResult($sheetIds, $command->getMessage() . 'printPlanning.success');

        $this->assertEquals($expected, $result);
    }

    public function testHandlePlanningAndBadge(): void
    {
        $event = $this->prophesize(Event::class);
        $sheetIds = [11, 12, 13, 14, 15, 16];
        $admin = $this->prophesize(Admin::class);
        $admin->getEmail()->shouldBeCalled()->willReturn('email@example.net');
        $orderBy = PlanningOrderedBy::ORDER_BY_SHEET_TITLE;
        $admin->getLocale()->willReturn('fr');

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $dateTime = new \DateTime();

        $extraData = new Event\ExtraData($event->reveal(), Type::ADMIN_SHEET_BATCH_IDS, '11,12,13,14,15,16', $dateTime);
        $extraDataRepository->add($extraData)->shouldBeCalled();

        $jobQueue
            ->printPlanning($extraData, $orderBy, 'email@example.net', 'fr', 'printPlanningAndBadge')
            ->shouldBeCalled()
        ;

        $handler = new PrintPlanningHandler(
            $jobQueue->reveal(),
            $extraDataRepository->reveal(),
            $dateTime
        );

        $command = new PrintPlanning($event->reveal(), $sheetIds, $admin->reveal(), $orderBy, 'fr', 'printPlanningAndBadge');
        $result = $handler->handle($command);

        $expected = new BatchResult($sheetIds, $command->getMessage() . 'printPlanningAndBadge.success');

        $this->assertEquals($expected, $result);
    }

    public function testHandleBadge(): void
    {
        $event = $this->prophesize(Event::class);
        $sheetIds = [11, 12, 13, 14, 15, 16];
        $admin = $this->prophesize(Admin::class);
        $admin->getEmail()->shouldBeCalled()->willReturn('email@example.net');
        $orderBy = PlanningOrderedBy::ORDER_BY_SHEET_TITLE;
        $admin->getLocale()->willReturn('fr');

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $dateTime = new \DateTime();

        $extraData = new Event\ExtraData($event->reveal(), Type::ADMIN_SHEET_BATCH_IDS, '11,12,13,14,15,16', $dateTime);
        $extraDataRepository->add($extraData)->shouldBeCalled();

        $jobQueue
            ->printPlanning($extraData, $orderBy, 'email@example.net', 'fr', 'printBadge')
            ->shouldBeCalled()
        ;

        $handler = new PrintPlanningHandler(
            $jobQueue->reveal(),
            $extraDataRepository->reveal(),
            $dateTime
        );

        $command = new PrintPlanning($event->reveal(), $sheetIds, $admin->reveal(), $orderBy, 'fr', 'printBadge');
        $result = $handler->handle($command);

        $expected = new BatchResult($sheetIds, $command->getMessage() . 'printBadge.success');

        $this->assertEquals($expected, $result);
    }
}
