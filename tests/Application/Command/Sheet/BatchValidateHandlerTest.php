<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchValidate;
use Proximum\Vimeet\Application\Command\Sheet\BatchValidateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchValidateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $date    = new \DateTime();
        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);
        $comment = 'truc muche';

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $batchJobQueue   = $this->prophesize(BatchJobQueueInterface::class);
        $jobQueue        = $this->prophesize(JobQueueInterface::class);

        $sheetRepository->getUnvalidatedSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $sheetRepository->updateStateBySheetsId([1, 2, 3], Sheet::STATE_VALIDATED)->shouldBeCalled();

        $jobQueue->indexSheets([1, 2, 3])->shouldBeCalled();

        $jobQueue->sendEmailing($event, [1, 2, 3], Events::SHEET_VALIDATED)->shouldBeCalled();

        $batchJobQueue->createJob(
            [1, 2, 3],
            $admin,
            ['comment' => $comment]
        )->shouldBeCalled();

        $command = new BatchValidate($event, [1, 2, 3], $admin, $comment);

        $handler = new BatchValidateHandler(
            $sheetRepository->reveal(),
            $batchJobQueue->reveal(),
            $jobQueue->reveal()
        );
        $result  = $handler->handle($command);

        $this->assertEquals(3, $result->count);
    }
}
