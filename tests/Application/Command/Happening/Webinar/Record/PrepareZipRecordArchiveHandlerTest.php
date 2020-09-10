<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareZipRecordArchive;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareZipRecordArchiveHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Happening;

class PrepareZipRecordArchiveHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $happening = $this->prophesize(Happening::class);
        $admin = $this->prophesize(Admin::class);

        $jobQueue
            ->zipRecordArchive(
                $happening->reveal(),
                true,
                $admin->reveal(),
                'fr'
            )
            ->shouldBeCalled()
        ;

        $command = new PrepareZipRecordArchive(
            $happening->reveal(),
            true,
            $admin->reveal(),
            'fr'
        );

        $handler = new PrepareZipRecordArchiveHandler($jobQueue->reveal());
        $handler->handle($command);
    }
}
