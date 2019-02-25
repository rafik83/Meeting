<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\AvailabilityType;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Unavailability\CreateUnavailabilities;
use Proximum\Vimeet\Application\Command\Unavailability\RemoveUserUnavailabilities;
use Proximum\Vimeet\Application\Components\Type\HasUnavailabilityManagementDisabled;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultsView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CreateUnavailabilitiesAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;
    
    /** @var CommandBusInterface */
    private $commandBus;
    
    /** @var HasUnavailabilityManagementDisabled */
    private $hasUnavailabilityManagementDisabled;
    
    public function __construct(
        CommandBusInterface $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        HasUnavailabilityManagementDisabled $hasUnavailabilityManagementDisabled
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationChecker = $authorizationChecker;
        $this->hasUnavailabilityManagementDisabled = $hasUnavailabilityManagementDisabled;
    }
    
    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Participant $participant,
        Sheet $sheet,
        UserDomain $userDomain
    ): JsonResponse {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();
        
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet)
            || !$this->authorizationChecker->isGranted(AgendaAccessVoter::PERMISSION, $event)
            || true === $this->hasUnavailabilityManagementDisabled->isSatisfiedBy($sheet)
            || null === $sheet->getParticipants()
        ) {
            throw new AccessDeniedHttpException();
        }

        // Remove old unavailabilities
        $this->commandBus->handle(new RemoveUserUnavailabilities($user, $event, $sheet));

        $payload = json_decode($request->getContent(), true);

        // Create new unavailabilities by days
        $createUnavailabilitiesResults = $this->commandBus->handle(
            new CreateUnavailabilities(
                $event,
                $sheet,
                $user,
                $event->getAvailableLocale($request->getLocale()),
                $participant,
                $payload['payload']
            )
        );

        $hasError = false;

        /** @var CreateUnavailabilitiesResultsView $createUnavailabilitiesResults */
        foreach ($createUnavailabilitiesResults->results as $createUnavailabilitiesResult) {
            if (false === $createUnavailabilitiesResult->success) {
                $hasError = true;

                break;
            }
        }

        return new JsonResponse([
            'success' => $hasError ? 'ko' : 'ok',
        ]);
    }
}
