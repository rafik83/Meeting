<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Broadcast;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\OpenStreamToPublicCommand;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Infrastructure\Adapter\CommandBus;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\Happening\BroadcastVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OpenStreamToPublicAction
{
    /** @var CommandBus */
    private $commandBus;
    /**
     * @var AuthorizationCheckerAdapterInterface
     */
    private $authorizationCheckerAdapter;
    /**
     * @var CanAccessToWebinar
     */
    private $canAccessToWebinar;

    public function __construct(
        CommandBusInterface $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanAccessToWebinar $canAccessToWebinar
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canAccessToWebinar = $canAccessToWebinar;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Happening $happening,
        UserDomain $userDomain
    ): JsonResponse {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->canAccessToWebinar->isSatisfiableBy($happening, $user)
            || $happening->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied.');
        }

        $this->commandBus->handle(new OpenStreamToPublicCommand($happening));

        return new JsonResponse(['status' => 'ok',]);
    }
}
