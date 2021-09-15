<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Application\Command\Sheet\AddCommentHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\CommercialStatusChanged;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;

class AddCommentHandlerTest extends TestCase
{
    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $author;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $commentRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    public function setUp()
    {
        $this->dateTime = new \DateTime();
        $this->author = $this->prophesize(Admin::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getCommercialStatus()->willReturn(CommercialStatus::STATUS_DO_NOT_CALL);
        $this->sheet->getReminderDate()->shouldBeCalled()->willReturn($this->dateTime);

        $this->commentRepository = $this->prophesize(CommentRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testHandleComment()
    {
        $addComment = new AddComment($this->sheet->reveal(), $this->author->reveal());
        $addComment->text = 'text';

        // expected
        $expectedComment = new Comment(
            $this->sheet->reveal(),
            $this->author->reveal(),
            'text',
            $this->dateTime
        );

        // mock
        $this->commentRepository->add($expectedComment)->shouldBeCalled();
        $this->sheetRepository->set(Argument::any())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $command = new AddCommentHandler(
            $this->sheetRepository->reveal(),
            $this->commentRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );

        $command->handle($addComment);
    }

    public function testHandleCommercialStatus()
    {
        $addComment = new AddComment($this->sheet->reveal(), $this->author->reveal());
        $addComment->text = null;
        $addComment->commercialStatus = CommercialStatus::STATUS_CANCELED;

        // expected
        $expectedComment = new Comment(
            $this->sheet->reveal(),
            $this->author->reveal(),
            'text',
            $this->dateTime
        );

        // mock
        $this->sheet->setCommercialStatus(CommercialStatus::STATUS_CANCELED)->shouldBeCalled();
        $this->commentRepository->add($expectedComment)->shouldNotBeCalled();
        $this->sheetRepository->set($this->sheet->reveal())->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(
                Events::SHEET_SET_COMMERCIAL_STATUS,
                new CommercialStatusChanged(
                    $this->sheet->reveal(),
                    $this->author->reveal(),
                    $this->dateTime
                )
            )
            ->shouldBeCalled();

        $command = new AddCommentHandler(
            $this->sheetRepository->reveal(),
            $this->commentRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );

        $command->handle($addComment);
    }

    public function testReminderDate()
    {
        $reminderDate = new \DateTime('2000-01-01');
        $addComment = new AddComment($this->sheet->reveal(), $this->author->reveal());
        $addComment->text = null;
        $addComment->reminderDate = $reminderDate;

        // expected
        $expectedComment = new Comment(
            $this->sheet->reveal(),
            $this->author->reveal(),
            'text',
            $this->dateTime
        );

        // mock
        $this->sheet->setReminderDate($reminderDate)->shouldBeCalled();
        $this->sheet->setCommercialStatus('foo')->shouldNotBeCalled();
        $this->commentRepository->add($expectedComment)->shouldNotBeCalled();
        $this->sheetRepository->set($this->sheet->reveal())->shouldBeCalled();

        $command = new AddCommentHandler(
            $this->sheetRepository->reveal(),
            $this->commentRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );

        $command->handle($addComment);
    }
}
