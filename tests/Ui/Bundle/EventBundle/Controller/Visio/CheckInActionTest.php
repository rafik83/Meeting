<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Visio;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\Event\ScanEventEntranceCommand;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\CheckInAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CheckInActionTest extends TestCase
{
    /** @var ObjectProphecy|DDayGuesser */
    private $dDayGuesser;
    /** @var ObjectProphecy */
    private $twig;
    /** @var ObjectProphecy|CommandBusInterface */
    private $commandBus;
    /** @var ObjectProphecy|UrlGeneratorInterface */
    private $urlGenerator;
    /** @var ObjectProphecy|AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;
    private \DateTime $dateTime;
    /** @var ObjectProphecy|IsParticipantVisio */
    private $isParticipantVisio;
    /** @var ObjectProphecy|Request */
    private $request;
    /** @var ObjectProphecy|User */
    private $user;
    /** @var ObjectProphecy|Participant */
    private $participant;
    private UserDomain $userDomain;
    /** @var ObjectProphecy|Event */
    private $event;
    /** @var ObjectProphecy|Sheet */
    private $sheet;

    protected function setUp(): void
    {
        // data input

        $this->request = $this->prophesize(Request::class);
        $this->user = $this->prophesize(User::class);
        $this->userDomain = new UserDomain($this->user->reveal());
        $this->event = $this->prophesize(Event::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getLocale()->willReturn('en');
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());

        // dependencies

        $this->dDayGuesser = $this->prophesize(DDayGuesser::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->dateTime = new \DateTime();
        $this->isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
    }

    public function test__invokeOnMethodGet(): void
    {
        // data input

        $this->request->getMethod()->willReturn('GET');
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(true);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(true);
        $this->twig->render('@Event/Visio/checkIn.html.twig', ['event' => $this->event->reveal()])
            ->willReturn('Hello there')
        ;

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new Response('Hello there'), $result);
    }

    public function test__invokeOnMethodPost(): void
    {
        // data input

        $this->request->getMethod()->willReturn('POST');
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(true);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(true);
        $this->commandBus->handle(
            new ScanEventEntranceCommand($this->event->reveal(), $this->user->reveal(), $this->dateTime)
        )->shouldBeCalled()
        ;
        $this->urlGenerator->generate('event', ['_locale' => 'en'])->willReturn('/somewhere');

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new RedirectResponse('/somewhere'), $result);
    }

    public function test__invokeNotAuthenticated(): void
    {
        // data input

        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(false);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(true);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(true);

        // run test

        $this->expectException(AccessDeniedException::class);

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new RedirectResponse('/somewhere'), $result);
    }

    public function test__invokeCanNotEditSheet(): void
    {
        // data input

        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(false);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(true);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(true);

        // run test

        $this->expectException(AccessDeniedException::class);

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new RedirectResponse('/somewhere'), $result);
    }

    public function test__invokeNotVisioParticipant(): void
    {
        // data input

        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(false);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(true);

        // run test

        $this->expectException(AccessDeniedException::class);

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new RedirectResponse('/somewhere'), $result);
    }

    public function test__invokeIsNotDDay(): void
    {
        // data input

        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(true);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(false);

        // run test

        $this->expectException(AccessDeniedException::class);

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new RedirectResponse('/somewhere'), $result);
    }

    public function test__invokeIsNotParticipant(): void
    {
        // data input

        $this->sheet->getUserParticipant($this->user->reveal())->willReturn(null);

        // dependencies

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);
        $this->isParticipantVisio->isSatisfiedBy($this->participant->reveal())->willReturn(true);
        $this->dDayGuesser->isItDDay($this->event->reveal())->willReturn(true);

        // run test

        $this->expectException(AccessDeniedException::class);

        $action = new CheckInAction(
            $this->dDayGuesser->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->urlGenerator->reveal(),
            $this->authorizationChecker->reveal(),
            $this->dateTime,
            $this->isParticipantVisio->reveal()
        );

        $result = $action->__invoke($this->request->reveal(), $this->sheet->reveal(), $this->userDomain);
        self::assertEquals(new RedirectResponse('/somewhere'), $result);
    }
}
