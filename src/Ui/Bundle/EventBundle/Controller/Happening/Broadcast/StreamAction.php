<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Broadcast;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartBroadcast;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StopBroadcast;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\Happening\BroadcastVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StreamAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CanAccessToWebinar */
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
            || !$this->authorizationCheckerAdapter->isGranted(BroadcastVoter::CAN_STREAM_BROADCAST, $happening)
        ) {
            throw new AccessDeniedException('Access denied.');
        }

        $message = null;
        $hlsUrl = null;
        $type = $request->request->get('type');
        $streamId = $request->request->get('streamId');

        switch ($request->request->get('action')) {
            case 'start':
                $hlsUrl = $this->commandBus->handle(new StartBroadcast($happening, $type, $streamId));
                $message = 'stream_started';
            break;
            case 'stop':
                $message = 'stream_stopped';
                $this->commandBus->handle(new StopBroadcast($happening, $type));
            break;
            default:
                throw new BadRequestHttpException('Unsupported action');
            break;
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => $message,
            'hlsUrl' => $hlsUrl,
        ]);
    }
}
