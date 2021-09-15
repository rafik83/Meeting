<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Order\Export\ExportJobCreator;
use Proximum\Vimeet\Application\Command\Order\Export\ExportJobCreatorHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ExportJobCreatorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $admin  = new Admin('email@email.fr', 'salt', 'password', 'fr', 'firstName', 'lastName', 'ROLE_SUPER_ADMIN', new \DateTime());
        $locale = 'fr';
        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->exportOrdersForEvent($event, $admin, $locale)->shouldBeCalled();

        $command = new ExportJobCreator($event, $admin, $locale);
        $handler = new ExportJobCreatorHandler($jobQueue->reveal());
        $handler->handle($command);
    }
}
