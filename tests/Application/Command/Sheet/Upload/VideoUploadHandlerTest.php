<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Upload;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoStorageInterface;
use Proximum\Vimeet\Application\Command\Sheet\Upload\VideoUpload;
use Proximum\Vimeet\Application\Command\Sheet\Upload\VideoUploadHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Template\TemplateObject\Video;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoUploadHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $videoStorage, $event;

    public function setUp(): void
    {
        $this->videoStorage = $this->prophesize(VideoStorageInterface::class);
        $this->event = $this->prophesize(Event::class);
    }
    public function testHandle(): void
    {
        $object = $this->prophesize(Video::class);
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'mp4'])
            ->getMock()
        ;
        $file->method('getMimeType')->willReturn('video/mp4');
        $object->getFile()->shouldBeCalled()->willReturn($file);
        $object->getPath()->shouldBeCalled()->willReturn('/path/to/file.mp4');
        $object->hasPath()->shouldBeCalled()->willReturn(true);

        $this->videoStorage->remove('/path/to/file.mp4')->shouldBeCalled();
        $this->videoStorage
            ->upload($this->event->reveal(), $file)
            ->shouldBeCalled()
            ->willReturn('/path/to/new/file.mp4')
        ;

        $handler = new VideoUploadHandler($this->videoStorage->reveal());
        $command = new VideoUpload($this->event->reveal(), $object->reveal());

        $result = $handler->handle($command);

        $expected = [
            'path' => '/path/to/new/file.mp4',
            'mime-type' => 'video/mp4'
        ];

        $this->assertEquals($expected, $result);
    }
}
