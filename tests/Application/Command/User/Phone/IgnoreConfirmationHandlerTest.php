<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmation;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmationHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IgnoreConfirmationHandlerTest extends TestCase
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var Event */
    private $event;

    /** @var string */
    private $name;

    /** @var Participant */
    private $participant;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var IgnoreConfirmationHandler */
    private $handler;

    /** @var IgnoreConfirmation */
    private $command;

    /** @var ExtraData */
    private $extraData;

    public function setUp()
    {
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->event = EventFactory::createEvent();
        $this->name  = Type::PHONE_CONFIRMATION_IGNORED;
        $this->participant  = ParticipantFactory::create(SheetFactory::create());
        $this->datetime = new \DateTime();
        $this->handler = new IgnoreConfirmationHandler($this->extraDataRepository->reveal(), $this->datetime);
        $this->command = new IgnoreConfirmation($this->event, $this->participant);
        $this->extraData = new ExtraData($this->participant->getUser(), $this->event, $this->name, '', $this->datetime);
    }

    public function testHandleAddExtra()
    {
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser($this->event, $this->name, $this->participant->getUser())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->extraDataRepository->add($this->extraData)->shouldBeCalled();

        $this->handler->handle($this->command);
    }

    public function testHandleNullExtra()
    {
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser($this->event, $this->name, $this->participant->getUser())
            ->shouldBeCalled()
            ->willReturn($this->extraData)
        ;

        $this->extraDataRepository->add($this->extraData)->shouldNotBeCalled();

        $this->handler->handle($this->command);
    }
}
