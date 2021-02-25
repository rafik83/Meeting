<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\AvailabilityType;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Unavailability\UpdateUnavailabilities;
use Proximum\Vimeet\Application\Components\Type\HasAvailabilityManagementEnabled;
use Proximum\Vimeet\Application\Components\Type\HasUnavailabilityManagementDisabled;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultsView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CreateUnavailabilitiesAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var HasUnavailabilityManagementDisabled */
    private $hasUnavailabilityManagementDisabled;

    /** @var HasAvailabilityManagementEnabled */
    private $hasAvailabilityManagementEnabled;

    private FlashBagInterface $flashBag;

    public function __construct(
        CommandBusInterface $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        HasUnavailabilityManagementDisabled $hasUnavailabilityManagementDisabled,
        HasAvailabilityManagementEnabled $hasAvailabilityManagementEnabled,
        FlashBagInterface $flashBag
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationChecker = $authorizationChecker;
        $this->hasUnavailabilityManagementDisabled = $hasUnavailabilityManagementDisabled;
        $this->hasAvailabilityManagementEnabled = $hasAvailabilityManagementEnabled;
        $this->flashBag = $flashBag;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Participant $participant,
        Sheet $sheet
    ): JsonResponse {
        $event = $eventDomain->getEvent();
        $user = $participant->getUser();

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet)
            || !$this->authorizationChecker->isGranted(AgendaAccessVoter::PERMISSION, $event)
            || null === $sheet->getParticipants()
            || !(!$this->hasUnavailabilityManagementDisabled->isSatisfiedBy($sheet)
                || $this->hasAvailabilityManagementEnabled->isSatisfiedBy($sheet))
        ) {
            throw new AccessDeniedHttpException();
        }

        $payload = json_decode($request->getContent(), true);

        // Create new unavailabilities by days
        $updateUnavailabilitiesResults = $this->commandBus->handle(
            new UpdateUnavailabilities(
                $event,
                $sheet,
                $user,
                $event->getAvailableLocale($request->getLocale()),
                $participant,
                $payload['payload']
            )
        );

        $hasError = false;

        /** @var CreateUnavailabilitiesResultsView $updateUnavailabilitiesResults */
        foreach ($updateUnavailabilitiesResults->results as $createUnavailabilitiesResult) {
            if (false === $createUnavailabilitiesResult->success) {
                $hasError = true;

                break;
            }
        }

        if (!$hasError) {
            $this->flashBag->add('success', 'flash.agenda.availability.ok_message_on_save');
        }

        return new JsonResponse([
            'success' => $hasError ? 'ko' : 'ok',
        ]);
    }
}
