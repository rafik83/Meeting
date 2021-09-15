<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddClick;
use Proximum\Vimeet\Application\Query\Sheet\Template\TemplateObjectUrlQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog\FollowLinkAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FollowLinkActionTest extends TestCase
{
    public function test__invokeWithoutUserAndNotGranted(): void
    {
        // data input

        $objectId = 123;
        $index = null;
        $event = $this->prophesize(Event::class);
        $event->getLocaleFallback()->willReturn('en');
        $eventDomain = new EventDomain($event->reveal());
        $userDomain = null;
        $sheet = $this->prophesize(Sheet::class);

        // dependencies

        $authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $commandBus = $this->prophesize(CommandBusInterface::class);

        $redirectUrl = 'http://fair.domain/en/';

        $queryBus->handle(new TemplateObjectUrlQuery($sheet->reveal(), $event->reveal(), 'en', $objectId, $index))
            ->willReturn($redirectUrl)
        ;

        $authorizationCheckerAdapter->isGranted(Argument::any())->willReturn(false);

        // run

        $action = new FollowLinkAction(
            $authorizationCheckerAdapter->reveal(),
            $queryBus->reveal(),
            $commandBus->reveal()
        );
        $result = $action->__invoke($sheet->reveal(), $objectId, $index, $eventDomain, $userDomain);

        self::assertEquals(new RedirectResponse($redirectUrl), $result);
    }

    public function test__invokeWithUserAndGranted(): void
    {
        // data input

        $objectId = 123;
        $index = null;
        $event = $this->prophesize(Event::class);
        $event->getLocaleFallback()->willReturn('en');
        $eventDomain = new EventDomain($event->reveal());
        $user = $this->prophesize(User::class);
        $userDomain = new UserDomain($user->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUserLocale($user->reveal())->willReturn('pt');

        // dependencies

        $authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $commandBus = $this->prophesize(CommandBusInterface::class);

        $redirectUrl = 'http://fair.domain/pt/';

        $queryBus->handle(new TemplateObjectUrlQuery($sheet->reveal(), $event->reveal(), 'pt', $objectId, $index))
            ->willReturn($redirectUrl)
        ;

        $authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $commandBus->handle(new AddClick($user->reveal(), $sheet->reveal(), $objectId, $index))->shouldBeCalled();

        // run

        $action = new FollowLinkAction(
            $authorizationCheckerAdapter->reveal(),
            $queryBus->reveal(),
            $commandBus->reveal()
        );
        $result = $action->__invoke($sheet->reveal(), $objectId, $index, $eventDomain, $userDomain);

        self::assertEquals(new RedirectResponse($redirectUrl), $result);
    }
}
