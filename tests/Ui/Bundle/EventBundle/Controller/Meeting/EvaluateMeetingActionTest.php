<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\EvaluateMeeting;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Contact\ContactView;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting\EvaluateMeetingAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\EvaluateMeetingType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

class EvaluateMeetingActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $engine,
        $commandBus,
        $router,
        $formFactory,
        $authorizationChecker,
        $queryBus,
        $request,
        $event,
        $user,
        $sheet,
        $meeting,
        $participant
    ;

    public function setUp(): void
    {
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->meeting = $this->prophesize(Meeting::class);
        $this->participant = $this->prophesize(Participant::class);

        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());
    }

    public function testInvoke(): void
    {
        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->meeting->isVisio()->shouldBeCalled()->willReturn(true);
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationChecker
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $command = new EvaluateMeeting(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->meeting->reveal(),
            $this->user->reveal()
        );

        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(EvaluateMeetingType::class, $command, [])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())
            ->shouldBeCalled()
        ;

        $metParticipant1 = $this->prophesize(Participant::class);
        $metParticipant2 = $this->prophesize(Participant::class);
        $metUser1 = $this->prophesize(User::class);
        $metUser2 = $this->prophesize(User::class);
        $metParticipant1->getUser()->shouldBeCalled()->willReturn($metUser1->reveal());
        $metParticipant2->getUser()->shouldBeCalled()->willReturn($metUser2->reveal());

        $this->meeting->getMetParticipants($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$metParticipant1->reveal(), $metParticipant2->reveal()])
        ;

        $contactView1 = $this->prophesize(ContactView::class);
        $contactView2 = $this->prophesize(ContactView::class);

        $this->queryBus
            ->handle(
                new GetContactViewQuery(
                    $this->event->reveal(),
                    $this->sheet->reveal(),
                    $this->participant->reveal(),
                    $metUser1->reveal(),
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn($contactView1->reveal())
        ;

        $this->queryBus
            ->handle(
                new GetContactViewQuery(
                    $this->event->reveal(),
                    $this->sheet->reveal(),
                    $this->participant->reveal(),
                    $metUser2->reveal(),
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn($contactView2->reveal())
        ;

        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');
        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $this->engine
            ->render('@Event/Meeting/evaluate-meeting.html.twig', [
                'event' => $this->event->reveal(),
                'sheet' => $this->sheet->reveal(),
                'participant' => $this->participant->reveal(),
                'ratingForm' => $formView->reveal(),
                'contacts' => [$contactView1->reveal(), $contactView2->reveal()],
            ])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new EvaluateMeetingAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal()
        );

        $eventDomain = new EventDomain($this->event->reveal());
        $userDomain = new UserDomain($this->user->reveal());
        $action(
            $this->request->reveal(),
            $eventDomain,
            $userDomain,
            $this->sheet->reveal(),
            $this->meeting->reveal()
        );
    }

    public function testInvokeHandle(): void
    {
        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->meeting->isVisio()->shouldBeCalled()->willReturn(true);
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationChecker
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $command = new EvaluateMeeting(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->meeting->reveal(),
            $this->user->reveal()
        );

        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(EvaluateMeetingType::class, $command, [])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())
            ->shouldBeCalled()
        ;

        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $form->createView()->shouldNotBeCalled();

        $this->queryBus
            ->handle(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->commandBus
            ->handle($command)
            ->shouldBeCalled()
        ;

        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $this->participant->getId()->shouldBeCalled()->willReturn(11);
        $this->router
            ->generate(Route::AGENDA_PARTICIPANT, [
                'sheet' => 12,
                'participant' => 11,
            ])->shouldBeCalled()
            ->willReturn('/route/to/agenda')
        ;

        $this->engine
            ->render('@Event/Meeting/evaluate-meeting.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new EvaluateMeetingAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal()
        );

        $eventDomain = new EventDomain($this->event->reveal());
        $userDomain = new UserDomain($this->user->reveal());
        $result = $action(
            $this->request->reveal(),
            $eventDomain,
            $userDomain,
            $this->sheet->reveal(),
            $this->meeting->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route/to/agenda', $result->getTargetUrl());
    }
}
