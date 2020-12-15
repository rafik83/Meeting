<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\UuidGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Adapter\GoogleCloudStorageAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\VideoStorageAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoStorageAdapterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $googleCloudStorageAdapter, $uuidGenerator, $fileStorage;

    public function setUp(): void
    {
        $this->googleCloudStorageAdapter = $this->prophesize(GoogleCloudStorageAdapter::class);
        $this->uuidGenerator = $this->prophesize(UuidGeneratorInterface::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
    }

    public function testUpload(): void
    {
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'mp4'])
            ->getMock()
        ;

        $file->method('guessExtension')->willReturn('mp4');

        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(123);

        $this->fileStorage->getContents($file)->shouldBeCalled()->willReturn('content');
        $this->uuidGenerator
            ->generate()
            ->shouldBeCalled()
            ->willReturn('a8bd2515-6c35-4674-a468-de9616f53af5')
        ;
        $this->googleCloudStorageAdapter
            ->create(
                'content',
                '/sheet-video/123/a8bd2515-6c35-4674-a468-de9616f53af5.mp4'
            )->shouldBeCalled()
        ;

        $adapter = new VideoStorageAdapter(
            $this->googleCloudStorageAdapter->reveal(),
            'https://video-cdn.vimeet.events',
            $this->uuidGenerator->reveal(),
            $this->fileStorage->reveal()
        );

        $result = $adapter->upload($event->reveal(), $file);

        $expected = 'https://video-cdn.vimeet.events/sheet-video/123/a8bd2515-6c35-4674-a468-de9616f53af5.mp4';

        $this->assertEquals($expected, $result);
    }

    public function testRemove(): void
    {
        $input = 'https://video-cdn.vimeet.events/sheet-video/123/a8bd2515-6c35-4674-a468-de9616f53af5.mp4';
        $adapter = new VideoStorageAdapter(
            $this->googleCloudStorageAdapter->reveal(),
            'https://video-cdn.vimeet.events',
            $this->uuidGenerator->reveal(),
            $this->fileStorage->reveal()
        );

        $this->googleCloudStorageAdapter
            ->delete('/sheet-video/123/a8bd2515-6c35-4674-a468-de9616f53af5.mp4')
            ->shouldBeCalled()
        ;

        $adapter->remove($input);
    }
}
