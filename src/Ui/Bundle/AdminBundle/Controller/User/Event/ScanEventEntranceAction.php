<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\User\Event\ScanEventEntranceCommand;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierToUserQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScanEventEntranceAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['identifier'], $data['scannedAt'])) {
            return new JsonResponse('Bad parameters', 400);
        }

        $user = $this->queryBus->handle(new QRCodeIdentifierToUserQuery($data['identifier']));

        if (!$user instanceof User) {
            return new JsonResponse('User not found', 400);
        }

        try {
            $this->commandBus->handle(
                new ScanEventEntranceCommand(
                    $event,
                    $user,
                    (new \DateTime($data['scannedAt']))
                        ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                )
            );
        } catch (\Exception $e) {
            return new JsonResponse('Bad datetime format', 400);
        }

        return new JsonResponse('ok');
    }
}
