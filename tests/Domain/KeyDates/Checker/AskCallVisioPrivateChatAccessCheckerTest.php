<?php


namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\AskCallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Repository\ChatSessionRepository;

class AskCallVisioPrivateChatAccessCheckerTest extends TestCase
{

    public function testAllowedAccessDateNull(): void
    {
        $event = $this->prophesize(Event::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepository::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $user = $this->prophesize(User::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->getCallVisioOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getCallVisioCloseDate()->shouldBeCalled()->willReturn(null);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());

        $askCallVisioPrivateChatAccessChecker = new AskCallVisioPrivateChatAccessChecker($chatSessionRepository->reveal(), new \DateTimeImmutable());
        $result = $askCallVisioPrivateChatAccessChecker->allowedToAccess($event->reveal(), $chatSession->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testNotAllowedAccessBefore(): void
    {
        $event = $this->prophesize(Event::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepository::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $user = $this->prophesize(User::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->getCallVisioOpenDate()->shouldBeCalled()->willReturn(\DateTimeImmutable::createFromFormat('!d/m/Y', '01/11/2020'));
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());

        $askCallVisioPrivateChatAccessChecker = new AskCallVisioPrivateChatAccessChecker(
            $chatSessionRepository->reveal(),
            \DateTimeImmutable::createFromFormat('!d/m/Y', '31/10/2020'));
        $result = $askCallVisioPrivateChatAccessChecker->allowedToAccess($event->reveal(), $chatSession->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testNotAllowedAccessAfter(): void
    {
        $event = $this->prophesize(Event::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepository::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $user = $this->prophesize(User::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->getCallVisioOpenDate()->shouldBeCalled()->willReturn(\DateTimeImmutable::createFromFormat('!d/m/Y', '01/11/2020'));
        $configuration->getCallVisioCloseDate()->shouldBeCalled()->willReturn(\DateTimeImmutable::createFromFormat('!d/m/Y', '03/11/2020'));
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());

        $askCallVisioPrivateChatAccessChecker = new AskCallVisioPrivateChatAccessChecker(
            $chatSessionRepository->reveal(),
            \DateTimeImmutable::createFromFormat('!d/m/Y', '04/11/2020'));
        $result = $askCallVisioPrivateChatAccessChecker->allowedToAccess($event->reveal(), $chatSession->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testAllowedAccess(): void
    {
        $event = $this->prophesize(Event::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepository::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $user = $this->prophesize(User::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->getCallVisioOpenDate()->shouldBeCalled()->willReturn(\DateTimeImmutable::createFromFormat('!d/m/Y', '01/11/2020'));
        $configuration->getCallVisioCloseDate()->shouldBeCalled()->willReturn(\DateTimeImmutable::createFromFormat('!d/m/Y', '03/11/2020'));
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $chatSessionRepository->hasMessageFromUser($chatSession->reveal(), $user->reveal())->shouldBeCalled()->willReturn(true);

        $askCallVisioPrivateChatAccessChecker = new AskCallVisioPrivateChatAccessChecker(
            $chatSessionRepository->reveal(),
            \DateTimeImmutable::createFromFormat('!d/m/Y', '02/11/2020'));
        $result = $askCallVisioPrivateChatAccessChecker->allowedToAccess($event->reveal(), $chatSession->reveal(), $user->reveal());

        self::assertTrue($result);
    }
}
