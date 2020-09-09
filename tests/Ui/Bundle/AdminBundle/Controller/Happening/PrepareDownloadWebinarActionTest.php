<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareZipRecordArchive;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordedFileAccessible;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\PrepareDownloadWebinarAction;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PrepareDownloadWebinarActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter, $commandBus, $isRecordedFileAccessible, $router, $flashBag;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->isRecordedFileAccessible = $this->prophesize(IsRecordedFileAccessible::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
    }

    public function testInvokeAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);
        $request = $this->prophesize(Request::class);
        $event = $this->prophesize(Event::class);
        $happening = $this->prophesize(Happening::class);
        $admin = $this->prophesize(Admin::class);
        $adminDomain = new AdminDomain($admin->reveal());

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new PrepareDownloadWebinarAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->isRecordedFileAccessible->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal()
        );

        $action($request->reveal(), $event->reveal(), $happening->reveal(), $adminDomain);
    }

    public function testInvokeAlreadyGeneratedUrl(): void
    {
        $request = $this->prophesize(Request::class);
        $event = $this->prophesize(Event::class);
        $happening = $this->prophesize(Happening::class);
        $admin = $this->prophesize(Admin::class);
        $adminDomain = new AdminDomain($admin->reveal());
        $happening->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->isRecordedFileAccessible->isSatisfiedBy($happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $happening->getWebinarRecordZipFileUrl()
            ->shouldBeCalled()
            ->willReturn('https://example.net/path/to/file.zip')
        ;

        $action = new PrepareDownloadWebinarAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->isRecordedFileAccessible->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($request->reveal(), $event->reveal(), $happening->reveal(), $adminDomain);

        self::assertEquals('https://example.net/path/to/file.zip', $result->getTargetUrl());
    }

    public function testInvoke(): void
    {
        $request = $this->prophesize(Request::class);
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(12);
        $happening = $this->prophesize(Happening::class);
        $admin = $this->prophesize(Admin::class);
        $adminDomain = new AdminDomain($admin->reveal());
        $happening->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->isRecordedFileAccessible->isSatisfiedBy($happening->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $request->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->commandBus
            ->handle(new PrepareZipRecordArchive($happening->reveal(), false, $admin->reveal(), 'fr'))
            ->shouldBeCalled()
        ;

        $this->flashBag->add('warning', 'flash.admin.happening.webinar.zip_record_archive.not_prepared')
            ->shouldBeCalled()
        ;

        $this->router->generate('admin_happening_list', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('happening/list');

        $action = new PrepareDownloadWebinarAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->isRecordedFileAccessible->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($request->reveal(), $event->reveal(), $happening->reveal(), $adminDomain);

        self::assertEquals('happening/list', $result->getTargetUrl());
    }
}
