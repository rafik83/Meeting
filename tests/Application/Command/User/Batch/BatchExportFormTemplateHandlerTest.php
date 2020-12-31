<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\User\Batch\BatchExportFormTemplate;
use Proximum\Vimeet\Application\Command\User\Batch\BatchExportFormTemplateHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class BatchExportFormTemplateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $formTemplate = $this->prophesize(FormTemplate::class);
        $admin = $this->prophesize(Admin::class);
        $date = new \DateTime();

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $extraData = new ExtraData(
            $event->reveal(),
            Type::ADMIN_USER_BATCH_IDS,
            '12,13,14,15,16,17,18,19',
            $date
        );
        $extraDataRepository
            ->add($extraData)
            ->shouldBeCalled()
        ;

        $jobQueue
            ->exportFormTemplateDataByUsers(
                $event->reveal(),
                $formTemplate->reveal(),
                $admin->reveal(),
                'fr',
                $extraData
            )
            ->shouldBeCalled()
        ;

        $handler = new BatchExportFormTemplateHandler(
            $extraDataRepository->reveal(),
            $jobQueue->reveal(),
            $date
        );

        $handler->handle(
            new BatchExportFormTemplate(
                $event->reveal(),
                $formTemplate->reveal(),
                $admin->reveal(),
                'fr',
                [12, 13, 14, 15, 16, 17, 18, 19]
            )
        );
    }
}
