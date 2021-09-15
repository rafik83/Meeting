<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssign;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssignHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchAssignHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $dateTime = new \DateTime();
        $user1    = new User('test@test.com', 'salt', 'password', 'fr');
        $user2    = new User('test@test.com', 'salt', 'password', 'fr');
        $user3    = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1   = new Sheet($event, $type, [], $user1, $dateTime);
        $sheet2   = new Sheet($event, $type, [], $user2, $dateTime);
        $sheet3   = new Sheet($event, $type, [], $user3, $dateTime);

        $organizer = $this->prophesize(Admin::class);
        $organizer->isOrganizer()->willReturn(true);
        $organizer->isOperator()->willReturn(false);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $sheetRepository
            ->batchAssignBySheetsId([1, 2, 3], $organizer->reveal())
            ->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexSheets([1, 2, 3])->shouldBeCalled();

        $command = new BatchAssign([1, 2, 3], $organizer->reveal());

        $handler = new BatchAssignHandler($sheetRepository->reveal(), $jobQueue->reveal());
        $result  = $handler->handle($command);

        $this->assertEquals(3, $result->count);
        $this->assertEquals($command->getMessage() . 'assign.success', $result->message);
    }

    public function testHandleUnAssigned()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $dateTime = new \DateTime();
        $user1    = new User('test@test.com', 'salt', 'password', 'fr');
        $user2    = new User('test@test.com', 'salt', 'password', 'fr');
        $user3    = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1   = new Sheet($event, $type, [], $user1, $dateTime);
        $sheet2   = new Sheet($event, $type, [], $user2, $dateTime);
        $sheet3   = new Sheet($event, $type, [], $user3, $dateTime);

        $organizer = $this->prophesize(Admin::class);
        $organizer->isOrganizer()->willReturn(true);
        $organizer->isOperator()->willReturn(false);

        $sheet1->assign($organizer->reveal());
        $sheet2->assign($organizer->reveal());
        $sheet3->assign($organizer->reveal());

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $sheetRepository->batchUnAssignBySheetsId([1, 2, 3])->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexSheets([1, 2, 3])->shouldBeCalled();

        $command = new BatchAssign([1, 2, 3], null);

        $handler = new BatchAssignHandler($sheetRepository->reveal(), $jobQueue->reveal());
        $result  = $handler->handle($command);

        $this->assertEquals(3, $result->count);
        $this->assertEquals($command->getMessage() . 'unassign.success', $result->message);
    }
}
