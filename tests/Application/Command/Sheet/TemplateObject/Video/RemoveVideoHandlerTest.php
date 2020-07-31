<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\TemplateObject\Video;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoStorageInterface;
use Proximum\Vimeet\Application\Command\Sheet\RemoveData;
use Proximum\Vimeet\Application\Command\Sheet\RemoveDataHandler;
use Proximum\Vimeet\Application\Command\Sheet\TemplateObject\Video\RemoveVideo;
use Proximum\Vimeet\Application\Command\Sheet\TemplateObject\Video\RemoveVideoHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Video;

class RemoveVideoHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $removeDataHandler, $videoStorage;

    public function setUp(): void
    {
        $this->removeDataHandler = $this->prophesize(RemoveDataHandler::class);
        $this->videoStorage = $this->prophesize(VideoStorageInterface::class);
    }

    public function testHandleNoPath(): void
    {
        $video = $this->prophesize(Video::class);
        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);

        $video->getPath()->shouldBeCalled()->willReturn(null);

        $this->videoStorage->remove(Argument::any())->shouldNotBeCalled();
        $this->removeDataHandler->handle(Argument::any())->shouldNotBeCalled();

        $handler = new RemoveVideoHandler(
            $this->removeDataHandler->reveal(),
            $this->videoStorage->reveal()
        );

        $command = new RemoveVideo($video->reveal(), $sheet->reveal(), $templateData->reveal());

        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $video = $this->prophesize(Video::class);
        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);

        $video->getPath()->shouldBeCalled()->willReturn('/path/to/file.mp4');

        $this->videoStorage->remove('/path/to/file.mp4')->shouldBeCalled();
        $removeData = new RemoveData(
            $templateData->reveal(),
            $video->reveal(),
            $sheet->reveal()
        );
        $this->removeDataHandler->handle($removeData)->shouldBeCalled();

        $handler = new RemoveVideoHandler(
            $this->removeDataHandler->reveal(),
            $this->videoStorage->reveal()
        );

        $command = new RemoveVideo($video->reveal(), $sheet->reveal(), $templateData->reveal());

        $handler->handle($command);
    }
}
