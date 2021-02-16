<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\Event\ScanEventEntranceCommand;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class CheckInAction
{
    /** @var DDayGuesser */
    private $dayGuesser;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        DDayGuesser $dayGuesser,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        \DateTimeInterface $dateTime,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->dayGuesser = $dayGuesser;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->dateTime = $dateTime;
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function __invoke(Request $request, Sheet $sheet, UserDomain $userDomain): Response
    {
        $event = $sheet->getEvent();
        $participant = $sheet->getUserParticipant($userDomain->getUser());

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$participant instanceof Participant
            || !$this->isParticipantVisio->isSatisfiedBy($participant)
            || false === $this->dayGuesser->isItDDay($event)) {
            throw new AccessDeniedException();
        }

        if ('POST' === $request->getMethod()) {
            $this->commandBus->handle(
                new ScanEventEntranceCommand(
                    $event,
                    $userDomain->getUser(),
                    $this->dateTime
                )
            );

            return new RedirectResponse(
                $this->urlGenerator->generate('event', ['_locale' => $participant->getLocale()])
            );
        }

        return new Response(
            $this->engine->render('@Event/Visio/checkIn.html.twig', [
                'event' => $event,
            ])
        );
    }
}
