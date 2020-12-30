<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Stay;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AddTasting;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AddTastingHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class AddTastingHandlerTest extends TestCase
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
        $this->handler = new AddTastingHandler($this->extraDataRepository->reveal(), $this->dateTime);
    }

    public function testHandleNotEmptyTasting()
    {
        // Expected behaviors
        $this->extraDataRepository
            ->removeForUserAndEventAndName(
                $this->user->reveal(),
                $this->event->reveal(),
                Type::ROOMING_TASTING
            )
            ->shouldBeCalled()
        ;

        $extraData = new ExtraData(
            $this->user->reveal(),
            $this->event->reveal(),
            Type::ROOMING_TASTING,
            'tastingToBeRemoved',
            $this->dateTime
        );

        $this->extraDataRepository
            ->add($extraData)
            ->shouldBeCalled()
        ;

        // Input
        $addTasting = new AddTasting(
            $this->event->reveal(),
            $this->user->reveal(),
            'tastingToBeRemoved'
        );

        // Run
        $this->handler->handle($addTasting);
    }

    public function testHandleWithEmptyTasting()
    {
        // Expected behaviors
        $this->extraDataRepository
            ->removeForUserAndEventAndName(
                $this->user->reveal(),
                $this->event->reveal(),
                Type::ROOMING_TASTING
            )
            ->shouldBeCalled()
        ;

        $this->extraDataRepository
            ->add(Argument::any())
            ->shouldNotBeCalled()
        ;

        // Input
        $addTasting = new AddTasting(
            $this->event->reveal(),
            $this->user->reveal(),
            ''
        );

        // Run
        $this->handler->handle($addTasting);
    }
}
