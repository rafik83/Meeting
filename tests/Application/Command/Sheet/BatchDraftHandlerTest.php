<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchDraft;
use Proximum\Vimeet\Application\Command\Sheet\BatchDraftHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchDraftHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $date   = new \DateTime();
        $admin  = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);
        $type   = new Type($event);
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);

        $sheet1->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
        $sheet2->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
        $sheet3->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);

        // Expected
        $expectedSheet1 = new Sheet($event, $type, [], $user1, $date);
        $expectedSheet1->setValidationState(Sheet::STATE_VALIDATION_DRAFT);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, $date);
        $expectedSheet2->setValidationState(Sheet::STATE_VALIDATION_DRAFT);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, $date);
        $expectedSheet3->setValidationState(Sheet::STATE_VALIDATION_DRAFT);

        $command = new BatchDraft($event, [1, 2, 3], $admin);

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $batchJobQueue   = $this->prophesize(BatchJobQueueInterface::class);
        $jobQueue        = $this->prophesize(JobQueueInterface::class);

        $sheetRepository->getSheetsById([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([$sheet1, $sheet2, $sheet3]);

        $sheetRepository->updateValidationState(
            [1, 2, 3],
            Sheet::STATE_VALIDATION_DRAFT
        )->shouldBeCalled();

        $batchJobQueue->createJob([1, 2, 3], $admin)->shouldBeCalled();

        $jobQueue->sendEmailing($event, [1, 2, 3], Events::SHEET_VALIDATION_DRAFT)->shouldBeCalled();

        // Handler
        $handler = new BatchDraftHandler(
            $sheetRepository->reveal(),
            $batchJobQueue->reveal(),
            $jobQueue->reveal()
        );

        $result = $handler->handle($command);

        $this->assertEquals(3, $result->count);
    }
}
