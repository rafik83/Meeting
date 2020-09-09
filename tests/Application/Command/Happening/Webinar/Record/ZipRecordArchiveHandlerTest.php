<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use DateTime;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\ZipRecordArchive;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\ZipRecordArchiveHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\MissingSessionIdException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\WebinarIsNotRecordedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\WebinarIsRecordingException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Psr\Log\LoggerInterface;

class ZipRecordArchiveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $zipRecordArchiveStorage, $fileSystem, $videoConferenceAdapter, $happeningRepository, $logger;

    public function setUp(): void
    {
        $this->zipRecordArchiveStorage = $this->prophesize(ZipRecordArchiveStorageInterface::class);
        $this->fileSystem = $this->prophesize(FileSystemAdapterInterface::class);
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testHandleWebinarNotRecorded(): void
    {
        $this->expectException(WebinarIsNotRecordedException::class);
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);

        $command = new ZipRecordArchive($happening->reveal(), true);

        $handler = new ZipRecordArchiveHandler(
            $this->zipRecordArchiveStorage->reveal(),
            $this->fileSystem->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->happeningRepository->reveal(),
            $this->logger->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleNoSessionId(): void
    {
        $this->expectException(MissingSessionIdException::class);
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn(null);

        $command = new ZipRecordArchive($happening->reveal(), true);

        $handler = new ZipRecordArchiveHandler(
            $this->zipRecordArchiveStorage->reveal(),
            $this->fileSystem->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->happeningRepository->reveal(),
            $this->logger->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleIsRecording(): void
    {
        $this->expectException(WebinarIsRecordingException::class);
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('session-id');

        $this->videoConferenceAdapter->isRecording('session-id')->shouldBeCalled()->willReturn(true);

        $command = new ZipRecordArchive($happening->reveal(), true);

        $handler = new ZipRecordArchiveHandler(
            $this->zipRecordArchiveStorage->reveal(),
            $this->fileSystem->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->happeningRepository->reveal(),
            $this->logger->reveal()
        );

        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $date = new DateTime();
        $category = $this->prophesize(Happening\Category::class);
        $happening = new Happening(
            $event->reveal(),
            $date,
            $date,
            $category->reveal(),
            [],
            true,
            null,
            null,
            true,
            false,
            false,
            null,
            true,
            true
        );
        $happening->addWebinarRecordZipFileUrl('/path-to-file.zip');
        $happening->setWebinarSessionId('session-id');

        $reflection  = new \ReflectionClass(Happening::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($happening, 12);
        $property->setAccessible(false);

        $expected = clone $happening;
        $expected->addWebinarRecordZipFileUrl('https://example.net/event/1/1234-1234-1234/multiple-archives/webinar-12.zip');

        $this->zipRecordArchiveStorage->delete('/path-to-file.zip')->shouldBeCalled();
        $urls = [
            'ebdb4fec-802f-433b-a428-4d5ab053cc15',
            '37d456d0-d2b4-43a9-901b-001edddb570d',
            '3789e4be-168c-49ac-9ad1-c853409d5b0f',
        ];
        $this->videoConferenceAdapter->listArchiveIds('session-id')->shouldBeCalled()->willReturn($urls);
        $this->videoConferenceAdapter->isRecording('session-id')->shouldBeCalled()->willReturn(false);

        $this->fileSystem->createTempDir()
            ->shouldBeCalled()
            ->willReturn('/tmp/vimeet/1234-1234-1234')
        ;

        $files = [
            'webinar-12-part1.mp4' => 'ebdb4fec-802f-433b-a428-4d5ab053cc15',
            'webinar-12-part2.mp4' => '37d456d0-d2b4-43a9-901b-001edddb570d',
            'webinar-12-part3.mp4' => '3789e4be-168c-49ac-9ad1-c853409d5b0f',
        ];
        $this->zipRecordArchiveStorage
            ->prepareZip(
                $files,
                '/tmp/vimeet/1234-1234-1234/webinar-12.zip'
            )
            ->shouldBeCalled()
        ;

        $uploadUrl = 'https://example.net/event/1/1234-1234-1234/multiple-archives/webinar-12.zip';
        $this->zipRecordArchiveStorage
            ->upload(
                '/tmp/vimeet/1234-1234-1234/webinar-12.zip',
                $event->reveal(),
                'multiple-archives/webinar-12.zip'
            )
            ->shouldBeCalled()
            ->willReturn($uploadUrl)
        ;

        $this->logger
            ->notice('VIMEET : Zip record archive of happening webinar 12 is uploaded on https://example.net/event/1/1234-1234-1234/multiple-archives/webinar-12.zip')
            ->shouldBeCalled()
        ;

        $this->fileSystem->remove('/tmp/vimeet/1234-1234-1234/webinar-12.zip')->shouldBeCalled();

        $this->happeningRepository->set($expected)->shouldBeCalled();

        $command = new ZipRecordArchive($happening, true);

        $handler = new ZipRecordArchiveHandler(
            $this->zipRecordArchiveStorage->reveal(),
            $this->fileSystem->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->happeningRepository->reveal(),
            $this->logger->reveal()
        );

        $handler->handle($command);
    }
}
