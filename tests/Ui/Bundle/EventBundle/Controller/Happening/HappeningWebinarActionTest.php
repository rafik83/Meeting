<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StartWebinarSessionCommand;
use Proximum\Vimeet\Application\Command\Scan\Happening\ScanHappening;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQuery;
use Proximum\Vimeet\Application\View\Happening\Webinar\ViewerWebinarView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\HappeningWebinarAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HappeningWebinarActionTest extends TestCase
{
    /** @var ObjectProphecy|AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy|CanAccessToWebinar */
    private $canAccessToWebinar;

    /** @var ObjectProphecy|EngineInterface */
    private $engine;

    /** @var ObjectProphecy|CommandBusInterface */
    private $commandBus;

    /** @var ObjectProphecy|QueryBusInterface */
    private $queryBus;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|EventDomain */
    private $eventDomain;

    /** @var ObjectProphecy|Request */
    private $request;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var User */
    private $user;

    /** @var UserDomain */
    private $userDomain;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var ObjectProphecy|Happening */
    private $happening;

    /** @var ObjectProphecy|PreviousHappeningEvaluationCheckerHandler */
    private $previousHappeningEvaluationCheckerHandler;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->canAccessToWebinar = $this->prophesize(CanAccessToWebinar::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->datetime = new \DateTime();
        $this->event = $this->prophesize(Event::class);
        $this->eventDomain = new EventDomain($this->event->reveal());
        $this->request = $this->prophesize(Request::class);
        $this->request->getQueryString()->willReturn(null);
        $this->request->getBaseUrl()->willReturn('');
        $this->request->getPathInfo()->willReturn('/happening/1');
        $this->user = $this->prophesize(User::class);
        $this->userDomain = new UserDomain($this->user->reveal());
        $this->sheet = $this->prophesize(Sheet::class);
        $this->happening = $this->prophesize(Happening::class);
        $this->happening->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->previousHappeningEvaluationCheckerHandler = $this->prophesize(PreviousHappeningEvaluationCheckerHandler::class);
    }

    public function testAccessDeniedWhenNotAuthenticated()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(false);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);

        $this->invokeController();
    }

    public function testAccessDeniedWhenPermissionNotGranted()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);

        $this->invokeController();
    }

    public function testAccessDeniedWhenSheetEditNotGranted()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);

        $this->invokeController();
    }

    public function testAccessDeniedWhenParticipationNotGranted()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);

        $this->invokeController();
    }

    public function testAccessDeniedWhenWebinarAccessIsDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $otherEvent = $this->prophesize(Event::class);
        $this->happening->getEvent()->willReturn($otherEvent->reveal());
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);

        $this->invokeController();
    }

    public function testAccessDeniedWhenHappeningEventIsIncorrect()
    {
        $this->expectException(AccessDeniedException::class);

        $otherEvent = $this->prophesize(Event::class);
        $this->happening->getEvent()->willReturn($otherEvent->reveal());

        $this->invokeController();
    }

    public function testAccessDeniedWhenSheetEventIsIncorrect()
    {
        $this->expectException(AccessDeniedException::class);

        $otherEvent = $this->prophesize(Event::class);
        $this->sheet->getEvent()->willReturn($otherEvent->reveal());
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);

        $this->invokeController();
    }

    public function testCommandsAreHandled()
    {
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);
        $this->previousHappeningEvaluationCheckerHandler->__invoke(Argument::any())->shouldBeCalled()->willReturn(null);
        $this->commandBus->handle(Argument::type(StartWebinarSessionCommand::class))->shouldBeCalled();
        $this->commandBus->handle(Argument::type(ScanHappening::class))->shouldBeCalled();

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $webinarView = $this->prophesize(ViewerWebinarView::class);
        $webinarView->reveal()->isSpeaker = false;
        $webinarView->isVideoWebinarAndHappeningIsEnded()->shouldBeCalled()->willReturn(false);

        $this->queryBus
            ->handle(Argument::type(GetWebinarViewQuery::class))
            ->shouldBeCalled()
            ->willReturn($webinarView->reveal())
        ;
        $account = $this->prophesize(Account::class);
        $account->getCompleteName()->shouldBeCalled()->willReturn('John Doe');
        $this->user->getAccount()->shouldBeCalled()->willReturn($account);
        $this->engine->render('@Event/Happening/webinar-viewer.html.twig', Argument::any())->shouldBeCalled()->willReturn('response body');

        $this->invokeController();
    }

    public function testRedirectIfMandatoryEvaluationIsMissing()
    {
        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_HAPPENING_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(ParticipationVoter::PARTICIPATE, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->willReturn(true);
        $redirect = $this->prophesize(RedirectResponse::class);
        $this->previousHappeningEvaluationCheckerHandler->__invoke(new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->happening->reveal(),
            '/happening/1'
        ))->shouldBeCalled()->willReturn($redirect->reveal());
        $this->commandBus->handle(Argument::type(StartWebinarSessionCommand::class))->shouldBeCalled();
        $this->commandBus->handle(Argument::type(ScanHappening::class))->shouldBeCalled();

        $response = $this->invokeController();
        $this->assertEquals($redirect->reveal(), $response);
    }

    private function invokeController()
    {
        $action = new HappeningWebinarAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->canAccessToWebinar->reveal(),
            $this->commandBus->reveal(),
            $this->engine->reveal(),
            $this->queryBus->reveal(),
            $this->datetime,
            $this->previousHappeningEvaluationCheckerHandler->reveal()
        );

        return $action(
            $this->request->reveal(),
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->happening->reveal(),
            $this->userDomain
        );
    }

}
