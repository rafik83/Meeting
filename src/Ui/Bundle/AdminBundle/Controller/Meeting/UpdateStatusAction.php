<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateStatus;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateStatusAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event, Meeting $meeting): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $meeting->getEvent()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $json = json_decode($request->getContent(), true);

        if (!isset($json['value']) || !\in_array($json['value'], Meeting::STATUS_LIST, true)) {
            return new JsonResponse('Bad parameter', 401);
        }

        $this->commandBus->handle(new UpdateStatus($meeting, $json['value']));

        return new JsonResponse();
    }
}
