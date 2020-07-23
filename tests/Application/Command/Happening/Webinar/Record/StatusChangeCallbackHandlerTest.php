<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Record;

use PhpParser\Node\Arg;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\StatusChangeCallback;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\StatusChangeCallbackHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class StatusChangeCallbackHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $recordArchiveRepository, $happeningRepository;

    /** @var \DateTime */
    private $dateTime;

    /** @var string */
    private $archiveId, $sessionId, $url;

    public function setUp(): void
    {
        $this->recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $this->dateTime = new \DateTime();

        $this->archiveId = 'archiveId';
        $this->sessionId = 'sessionId';
        $this->url = 'http://example.vimeet.proximum/path';
    }

    public function test_handle_started_not_known(): void
    {
        $this->recordArchiveRepository->getByArchiveId($this->archiveId)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $happening = $this->prophesize(Happening::class);
        $this->happeningRepository->findWebinarBySessionId($this->sessionId)
            ->shouldBeCalled()
            ->willReturn($happening)
        ;

        $recordArchive = new RecordArchive(
            $happening->reveal(),
            $this->archiveId,
            $this->dateTime
        );

        $this->recordArchiveRepository->add($recordArchive)->shouldBeCalled();

        $command = new StatusChangeCallback(
            $this->archiveId,
            $this->sessionId,
            'started',
            $this->url
        );

        $handler = new StatusChangeCallbackHandler(
            $this->recordArchiveRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function test_handle_stopped_not_known(): void
    {
        $this->recordArchiveRepository->getByArchiveId($this->archiveId)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $happening = $this->prophesize(Happening::class);
        $this->happeningRepository->findWebinarBySessionId($this->sessionId)
            ->shouldBeCalled()
            ->willReturn($happening)
        ;

        $recordArchive = new RecordArchive(
            $happening->reveal(),
            $this->archiveId,
            $this->dateTime
        );
        $recordArchive->stop();
        $recordArchive->addPathToRecordArchive($this->url);

        $this->recordArchiveRepository->add($recordArchive)->shouldBeCalled();

        $command = new StatusChangeCallback(
            $this->archiveId,
            $this->sessionId,
            'stopped',
            $this->url
        );

        $handler = new StatusChangeCallbackHandler(
            $this->recordArchiveRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function test_handle_unknown(): void
    {
        $this->recordArchiveRepository->getByArchiveId($this->archiveId)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->happeningRepository->findWebinarBySessionId($this->sessionId)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->recordArchiveRepository->add(Argument::any())->shouldNotBeCalled();
        $this->recordArchiveRepository->update(Argument::any())->shouldNotBeCalled();

        $command = new StatusChangeCallback(
            $this->archiveId,
            $this->sessionId,
            'stopped',
            $this->url
        );

        $handler = new StatusChangeCallbackHandler(
            $this->recordArchiveRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function test_handle_stopped_for_started_archive(): void
    {
        $happening = $this->prophesize(Happening::class);
        $recordArchive = new RecordArchive(
            $happening->reveal(),
            $this->archiveId,
            $this->dateTime
        );
        $this->recordArchiveRepository->getByArchiveId($this->archiveId)
            ->shouldBeCalled()
            ->willReturn($recordArchive)
        ;

        $this->happeningRepository->findWebinarBySessionId($this->sessionId)
            ->shouldNotBeCalled()
        ;

        $expected = clone $recordArchive;
        $expected->stop();
        $expected->addPathToRecordArchive($this->url);

        $this->recordArchiveRepository->update($expected)->shouldBeCalled();

        $command = new StatusChangeCallback(
            $this->archiveId,
            $this->sessionId,
            'stopped',
            $this->url
        );

        $handler = new StatusChangeCallbackHandler(
            $this->recordArchiveRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function test_handle_stopped_for_already_stopped(): void
    {
        $happening = $this->prophesize(Happening::class);
        $recordArchive = new RecordArchive(
            $happening->reveal(),
            $this->archiveId,
            $this->dateTime
        );
        $recordArchive->stop();

        $this->recordArchiveRepository->getByArchiveId($this->archiveId)
            ->shouldBeCalled()
            ->willReturn($recordArchive)
        ;

        $this->happeningRepository->findWebinarBySessionId($this->sessionId)
            ->shouldNotBeCalled()
        ;

        $this->recordArchiveRepository->update(Argument::any())->shouldNotBeCalled();

        $command = new StatusChangeCallback(
            $this->archiveId,
            $this->sessionId,
            'stopped',
            $this->url
        );

        $handler = new StatusChangeCallbackHandler(
            $this->recordArchiveRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function test_handle_un_stop(): void
    {
        $happening = $this->prophesize(Happening::class);
        $recordArchive = new RecordArchive(
            $happening->reveal(),
            $this->archiveId,
            $this->dateTime
        );
        $recordArchive->stop();

        $this->recordArchiveRepository->getByArchiveId($this->archiveId)
            ->shouldBeCalled()
            ->willReturn($recordArchive)
        ;

        $this->happeningRepository->findWebinarBySessionId($this->sessionId)
            ->shouldNotBeCalled()
        ;

        $expected = clone $recordArchive;
        $expected->unstop();
        $this->recordArchiveRepository->update($expected)->shouldBeCalled();

        $command = new StatusChangeCallback(
            $this->archiveId,
            $this->sessionId,
            'paused',
            $this->url
        );

        $handler = new StatusChangeCallbackHandler(
            $this->recordArchiveRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }
}
