<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSORedirectionAfterLoginResolver;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSORegistrationTypeResolver;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SSORedirectionAfterLoginResolverTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $type = $this->prophesize(Type::class);
        $type->getId()->willReturn(1337);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsByUserAndEvent($user, $event->reveal())->shouldBeCalled()->willReturn([]);

        $SSORegistrationTypeResolver = $this->prophesize(SSORegistrationTypeResolver::class);
        $SSORegistrationTypeResolver->handle($event->reveal())->shouldBeCalled()->willReturn($type->reveal());

        $router = $this->prophesize(RouterInterface::class);
        $router
            ->generate('event_participate', ['typeView' => 1337])
            ->shouldBeCalled()
            ->willReturn('/participate/type/1337')
        ;

        $SSORedirectionAfterLoginResolver = new SSORedirectionAfterLoginResolver(
            $sheetRepository->reveal(),
            $SSORegistrationTypeResolver->reveal(),
            $router->reveal()
        );
        $this->assertEquals(
            '/participate/type/1337',
            $SSORedirectionAfterLoginResolver->handle($event->reveal(), $user->reveal())
        );
    }
}
