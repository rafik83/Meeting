<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Broadcast;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartViewer;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetBroadcastViewQuery;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ViewAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    public function __construct(
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanAccessToWebinar $canAccessToWebinar
    ) {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canAccessToWebinar = $canAccessToWebinar;
    }

    public function __invoke(
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

        $hlsUrl = $this->queryBus->handle(new GetBroadcastViewQuery($happening));

        $this->commandBus->handle(new StartViewer($happening));

        return new JsonResponse([
            'status' => 'ok',
            'hlsUrl' => $hlsUrl,
        ]);
    }
}
