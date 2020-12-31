<?php

namespace Proximum\Vimeet\Tests\Domain\Token;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationTokenCreated;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UniqidGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserEventTokenGeneratorTest extends TestCase
{
    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $uniqidGenerator;

    /** @var ObjectProphecy */
    private $userEventTokenRepository;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->dateTime = new \DateTime();
        $this->userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $this->uniqidGenerator = $this->prophesize(UniqidGenerator::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testGetUserEventTokenForConfirmAgendaExistingToken()
    {
        // context
        $type = UserEventTokenType::AGENDA_CONFIRMATION;
        $userEventToken = new UserEventToken(
            $this->event->reveal(),
            $this->user->reveal(),
            $type,
            'token',
            $this->dateTime
        );

        // Expected
        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, $type)
            ->shouldBeCalled()
            ->willReturn($userEventToken);
        $this->uniqidGenerator->generate()->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        // Generator
        $generator = new UserEventTokenGenerator(
            $this->userEventTokenRepository->reveal(),
            $this->uniqidGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );
        $result = $generator->getUserEventTokenForConfirmAgenda($this->event->reveal(), $this->user->reveal(), $type);

        $this->assertEquals($userEventToken, $result);
    }

    public function testGetUserEventTokenForConfirmAgenda()
    {
        $type = UserEventTokenType::AGENDA_CONFIRMATION;
        $uniqid = uniqid(mt_rand());
        $this->user->getId()->shouldBeCalled()->willReturn(456);
        $this->event->getId()->shouldBeCalled()->willReturn(123);

        $token = hash('sha1', sprintf('%s%s%s%s%s', 123, 456, $type, $this->dateTime->format('c'), $uniqid));
        $expectedUserEventToken = new UserEventToken(
            $this->event->reveal(),
            $this->user->reveal(),
            $type,
            $token,
            $this->dateTime
        );

        // Expected
        $this->userEventTokenRepository
            ->findByEventAndUserAndType($this->event, $this->user, $type)
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->userEventTokenRepository->add($expectedUserEventToken)->shouldBeCalled();
        $this->uniqidGenerator->generate()->shouldBeCalled()->willReturn($uniqid);
        $this->eventDispatcher
            ->dispatch(
                Events::USER_EVENT_TOKEN_AGENDA_CONFIRMATION_CREATED,
                new AgendaConfirmationTokenCreated($this->event->reveal(), $this->user->reveal())
            )->shouldBeCalled()
        ;

        $generator = new UserEventTokenGenerator(
            $this->userEventTokenRepository->reveal(),
            $this->uniqidGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );
        $result = $generator->getUserEventTokenForConfirmAgenda($this->event->reveal(), $this->user->reveal(), $type);

        $this->assertEquals($token, $result->getToken());
        $this->assertEquals($type, $result->getType());
        $this->assertEquals($expectedUserEventToken, $result);
    }
}
