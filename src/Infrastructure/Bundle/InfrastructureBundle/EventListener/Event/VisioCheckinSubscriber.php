<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Event;

use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VisioCheckinSubscriber implements EventSubscriberInterface
{
    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /** @var DDayGuesser */
    private $dayGuesser;

    /** @var DDayGuesser */
    private $urlGenerator;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        EventByHostResolver $eventByHostResolver,
        DDayGuesser $dayGuesser,
        UrlGeneratorInterface $urlGenerator,
        ScanRepositoryInterface $scanRepository,
        SheetRepositoryInterface $sheetRepository,
        TokenStorageInterface $tokenStorage,
        \DateTimeInterface $dateTime,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->eventByHostResolver = $eventByHostResolver;
        $this->dayGuesser = $dayGuesser;
        $this->urlGenerator = $urlGenerator;
        $this->scanRepository = $scanRepository;
        $this->sheetRepository = $sheetRepository;
        $this->tokenStorage = $tokenStorage;
        $this->dateTime = $dateTime;
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function onKernelRequest(GetResponseEvent $responseEvent): void
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface || !$token->getUser() instanceof User) {
            return;
        }

        $request = $responseEvent->getRequest();
        $sheet = $this->sheetRepository->getSheetById($request->attributes->get('sheet'));

        if (!$sheet instanceof Sheet
            || 'event_visio_checkin' === $request->attributes->get('_route')) {
            return;
        }

        $participant = $sheet->getUserParticipant($token->getUser());

        if (!$participant instanceof Participant || !$this->isParticipantVisio->isSatisfiedBy($participant)) {
            return;
        }

        try {
            $event = $this->eventByHostResolver->resolveEventFromHostAndLocale(
                $request->getHost(),
                $request->getLocale()
            );

            if (false === $this->dayGuesser->isItDDay($event)) {
                return;
            }

            if ($this->scanRepository->isUserCheckinTodayByEvent($token->getUser(), $event, $this->dateTime)) {
                return;
            }

            $responseEvent->setResponse(
                new RedirectResponse(
                    $this->urlGenerator->generate('event_visio_checkin', ['sheet' => $sheet->getId()])
                )
            );
        } catch (\Exception $exception) {
            return;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
