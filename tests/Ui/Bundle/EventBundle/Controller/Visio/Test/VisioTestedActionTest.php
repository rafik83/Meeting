<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Visio\Test;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\VideoConference\SetVisioTested;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test\VisioTestedAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;

class VisioTestedActionTest extends TestCase
{
    public function testAction(): void
    {
        $request = $this->prophesize(Request::class);

        $event = $this->prophesize(Event::class);
        $eventDomain = $this->prophesize(EventDomain::class);
        $eventDomain->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $user = $this->prophesize(User::class);
        $userDomain = $this->prophesize(UserDomain::class);
        $userDomain->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(true);

        $commandBus = $this->prophesize(CommandBusInterface::class);
        $commandBus->handle(new SetVisioTested($event->reveal(), $user->reveal()))->shouldBeCalled();

        $visioTestedAction = new VisioTestedAction($authorizationCheckerAdapter->reveal(), $commandBus->reveal());
        $visioTestedAction($request->reveal(), $eventDomain->reveal(), $userDomain->reveal());
    }
}
