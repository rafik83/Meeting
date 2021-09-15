<?php

namespace Proximum\Vimeet\Tests\Application\Command\Contact;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Contact\Add;
use Proximum\Vimeet\Application\Command\Contact\AddHandler;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class AddHandlerTest extends TestCase
{
    /** @var ObjectProphecy|ContactRepositoryInterface */
    private $contactRepository;

    /** @var \DateTime */
    private $dateTime;

    /** @var AddHandler */
    private $addHandler;

    /** @var ObjectProphecy|User */
    private $user;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|User */
    private $contact;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->contact = $this->prophesize(User::class);

        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $this->dateTime = new \DateTime();

        $this->addHandler = new AddHandler($this->contactRepository->reveal(), $this->dateTime);
    }

    public function testContactAlreadyExists()
    {
        $this->user->getId()->shouldBeCalled()->willReturn(1);
        $this->contact->getId()->shouldBeCalled()->willReturn(2);

        $expectedContact = new Contact(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->contact->reveal(),
            $this->dateTime,
            'scan'
        );
        $this->contactRepository->find($expectedContact)->shouldBeCalled()->willReturn($expectedContact);
        $this->contactRepository->add($expectedContact)->shouldNotBeCalled();

        $this->addHandler->handle(new Add($this->event->reveal(), $this->user->reveal(), $this->contact->reveal(), 'scan'));
    }

    public function testDoNotAddMyself()
    {
        $this->user->getId()->shouldBeCalled()->willReturn(1);
        $this->contact->getId()->shouldBeCalled()->willReturn(1);

        $this->contactRepository->find(Argument::any())->shouldNotBeCalled();
        $this->contactRepository->add(Argument::any())->shouldNotBeCalled();

        $this->addHandler->handle(new Add($this->event->reveal(), $this->user->reveal(), $this->contact->reveal(), 'scan'));
    }

    public function testAdd()
    {
        $this->user->getId()->shouldBeCalled()->willReturn(1);
        $this->contact->getId()->shouldBeCalled()->willReturn(2);

        $expectedContact = new Contact(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->contact->reveal(),
            $this->dateTime,
            'scan'
        );
        $this->contactRepository->find($expectedContact)->shouldBeCalled()->willReturn(null);
        $this->contactRepository->add($expectedContact)->shouldBeCalled();

        $this->addHandler->handle(new Add($this->event->reveal(), $this->user->reveal(), $this->contact->reveal(), 'scan'));
    }
}
