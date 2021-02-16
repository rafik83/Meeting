<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Stay;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AddComment;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AddCommentHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class AddCommentHandlerTest extends TestCase
{
    private $extraDataRepository;

    private $user;

    private $event;

    private $handler;

    private $dateTime;

    public function setUp()
    {
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->dateTime = new \DateTime();
        $this->handler = new AddCommentHandler($this->extraDataRepository->reveal(), $this->dateTime);
    }

    public function testHandleNotEmptyComment()
    {
        // Expected behaviors
        $this->extraDataRepository
            ->removeForUserAndEventAndName(
                $this->user->reveal(),
                $this->event->reveal(),
                Type::ROOMING_COMMENT
            )
            ->shouldBeCalled()
        ;

        $extraData = new ExtraData(
            $this->user->reveal(),
            $this->event->reveal(),
            Type::ROOMING_COMMENT,
            'CommentToBeRemoved',
            $this->dateTime
        );

        $this->extraDataRepository
            ->add($extraData)
            ->shouldBeCalled()
        ;

        // Input
        $addComment = new AddComment(
            $this->event->reveal(),
            $this->user->reveal(),
            'CommentToBeRemoved'
        );

        // Run
        $this->handler->handle($addComment);
    }

    public function testHandleWithEmptyComment()
    {
        // Expected behaviors
        $this->extraDataRepository
            ->removeForUserAndEventAndName(
                $this->user->reveal(),
                $this->event->reveal(),
                Type::ROOMING_COMMENT
            )
            ->shouldBeCalled()
        ;

        $this->extraDataRepository
            ->add(Argument::any())
            ->shouldNotBeCalled()
        ;

        // Input
        $addComment = new AddComment(
            $this->event->reveal(),
            $this->user->reveal(),
            ''
        );

        // Run
        $this->handler->handle($addComment);
    }
}
