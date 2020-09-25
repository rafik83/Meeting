<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Route;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Home\HomeDispatch;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Application\View\Home\HomeDispatchView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\IsValidatedRequiredPackageMissing;
use Proximum\Vimeet\Domain\Transaction\IsValidatedTransactionMissing;
use Proximum\Vimeet\Infrastructure\Adapter\AuthorizationCheckerAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\HomeUserDispatcher;

class HomeUserDispatcherTest extends TestCase
{
    private $router,
        $homeDispatch,
        $homeDispatchAnonymousUser,
        $authorizationChecker,
        $dDayGuesser,
        $agendaAccessChecker,
        $isValidatedTransactionMissing,
        $isValidatedRequiredPackageMissing
    ;

    public function setUp(): void
    {
        $this->router = $this->prophesize(Router::class);
        $this->homeDispatch = $this->prophesize(HomeDispatch::class);
        $this->homeDispatchAnonymousUser = $this->prophesize(HomeDispatchAnonymousUser::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapter::class);
        $this->dDayGuesser = $this->prophesize(DDayGuesser::class);
        $this->agendaAccessChecker = $this->prophesize(AgendaAccessChecker::class);
        $this->isValidatedTransactionMissing = $this->prophesize(IsValidatedTransactionMissing::class);
        $this->isValidatedRequiredPackageMissing = $this->prophesize(IsValidatedRequiredPackageMissing::class);
    }

    public function testAttemptDispatchMultiSheet(): void
    {

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $homeDispatchView = $this->prophesize(HomeDispatchView::class);
        $group = $this->prophesize(Sheet\Group::class);
        $group->getId()->willReturn(2);
        $homeDispatchView->getGroup()->shouldBeCalled()->willReturn($group);
        $homeDispatchView->isGroup()->shouldBeCalled()->willReturn(true);

        $this->router->generate('event_sheet_group_index', ['sheetGroup' => 2])
            ->shouldBeCalled()
            ->willReturn('/sheets-group/2');

        $this->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->homeDispatch->handle($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($homeDispatchView->reveal());

        $this->dDayGuesser->isItDDay($event->reveal())->shouldNotBeCalled();
        $this->agendaAccessChecker->allowedToAccess($event->reveal())->shouldNotBeCalled();

        $dispatcher = new HomeUserDispatcher(
            $this->router->reveal(),
            $this->homeDispatch->reveal(),
            $this->homeDispatchAnonymousUser->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dDayGuesser->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal()
        );

        $response = $dispatcher->attemptDispatchUser($event->reveal(), $user->reveal());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/sheets-group/2', $response->getTargetUrl());
    }

    public function testAttemptDispatchUserNotDDay(): void
    {

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $homeDispatchView = $this->prophesize(HomeDispatchView::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $homeDispatchView->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $homeDispatchView->isGroup()->shouldBeCalled()->willReturn(false);
        $homeDispatchView->isOneSheet()->shouldBeCalled()->willReturn(true);

        $this->router->generate('event_sheet_default', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1');

        $this->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->homeDispatch->handle($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($homeDispatchView->reveal());

        $this->dDayGuesser->isItDDay($event->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->agendaAccessChecker->allowedToAccess($event->reveal())->shouldNotBeCalled();

        $dispatcher = new HomeUserDispatcher(
            $this->router->reveal(),
            $this->homeDispatch->reveal(),
            $this->homeDispatchAnonymousUser->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dDayGuesser->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal()
        );

        $response = $dispatcher->attemptDispatchUser($event->reveal(), $user->reveal());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/sheet/1', $response->getTargetUrl());
    }

    public function testAttemptDispatchUserDDayWithMissingPackage(): void
    {

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $homeDispatchView = $this->prophesize(HomeDispatchView::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $homeDispatchView->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $homeDispatchView->isGroup()->shouldBeCalled()->willReturn(false);
        $homeDispatchView->isOneSheet()->shouldBeCalled()->willReturn(true);

        $this->router->generate('event_sheet_default', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1');

        $this->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->homeDispatch->handle($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($homeDispatchView->reveal());

        $this->dDayGuesser->isItDDay($event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        // Not called as package missing first called
        $this->agendaAccessChecker->allowedToAccess($event->reveal())->shouldNotBeCalled();
        $this->isValidatedTransactionMissing->isSatisfiedBy($sheet->reveal())->shouldNotBeCalled();

        $dispatcher = new HomeUserDispatcher(
            $this->router->reveal(),
            $this->homeDispatch->reveal(),
            $this->homeDispatchAnonymousUser->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dDayGuesser->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal()
        );

        $response = $dispatcher->attemptDispatchUser($event->reveal(), $user->reveal());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/sheet/1', $response->getTargetUrl());
    }

    public function testAttemptDispatchUserDDay(): void
    {

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $homeDispatchView = $this->prophesize(HomeDispatchView::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $homeDispatchView->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $homeDispatchView->isGroup()->shouldBeCalled()->willReturn(false);
        $homeDispatchView->isOneSheet()->shouldBeCalled()->willReturn(true);

        $this->router->generate('event_agenda', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1/agenda');

        $this->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->homeDispatch->handle($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($homeDispatchView->reveal());

        $this->dDayGuesser->isItDDay($event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->agendaAccessChecker->allowedToAccess($event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->isValidatedTransactionMissing->isSatisfiedBy($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $dispatcher = new HomeUserDispatcher(
            $this->router->reveal(),
            $this->homeDispatch->reveal(),
            $this->homeDispatchAnonymousUser->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dDayGuesser->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal()
        );

        $response = $dispatcher->attemptDispatchUser($event->reveal(), $user->reveal());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/sheet/1/agenda', $response->getTargetUrl());
    }
}
