<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Configuration\Background;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImage;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImageHandler;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveImageHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setBackgroundImage('toto.jpg');

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->remove('toto.jpg')->shouldBeCalled();

        (new RemoveImageHandler($fileStorage->reveal()))->handle(new RemoveImage($event));

        $this->assertNull($event->getConfiguration()->getBackgroundImage());
    }
}
