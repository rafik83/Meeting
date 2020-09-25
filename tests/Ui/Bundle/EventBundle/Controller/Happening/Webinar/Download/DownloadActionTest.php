<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Happening\Webinar\Download;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordedFileAccessibleForUser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Webinar\Download\DownloadAction;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter, $isRecordedFileAccessibleForUser, $event, $happening;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->isRecordedFileAccessibleForUser = $this->prophesize(IsRecordedFileAccessibleForUser::class);
        $this->event = $this->prophesize(Event::class);
        $this->happening = $this->prophesize(Happening::class);
    }

    public function testInvokeNotConnected(): void
    {
        $this->expectException(AccessDeniedException::class);
        $eventDomain = new EventDomain($this->event->reveal());

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->isRecordedFileAccessibleForUser->reveal()
        );

        $action($eventDomain, $this->happening->reveal(), null);
    }

    public function testInvokeHappeningNotAvailable(): void
    {
        $this->expectException(AccessDeniedException::class);
        $eventDomain = new EventDomain($this->event->reveal());

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->isRecordedFileAccessibleForUser->reveal()
        );

        $action($eventDomain, $this->happening->reveal(), null);
    }

    public function testInvokeUserDomainNull(): void
    {
        $this->expectException(AccessDeniedException::class);

        $eventDomain = new EventDomain($this->event->reveal());
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event);

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->isRecordedFileAccessibleForUser->reveal()
        );

        $action($eventDomain, $this->happening->reveal(), null);
    }

    public function testInvokeFileNotAccessible(): void
    {
        $this->expectException(AccessDeniedException::class);
        $eventDomain = new EventDomain($this->event->reveal());
        $user = $this->prophesize(User::class);
        $userDomain = new UserDomain($user->reveal());
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->isRecordedFileAccessibleForUser
            ->isSatisfiedBy($this->happening->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->isRecordedFileAccessibleForUser->reveal()
        );

        $action($eventDomain, $this->happening->reveal(), $userDomain);
    }

    public function testInvokeAccessible(): void
    {
        $eventDomain = new EventDomain($this->event->reveal());
        $user = $this->prophesize(User::class);
        $userDomain = new UserDomain($user->reveal());
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->isRecordedFileAccessibleForUser
            ->isSatisfiedBy($this->happening->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->happening->getWebinarRecordZipFileUrl()
            ->shouldBeCalled()
            ->willReturn('https://example.net/file.zip')
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->isRecordedFileAccessibleForUser->reveal()
        );

        $result = $action($eventDomain, $this->happening->reveal(), $userDomain);

        $this->assertEquals('https://example.net/file.zip', $result->getTargetUrl());
    }
}
