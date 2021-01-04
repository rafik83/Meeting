<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignRoomNumber;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AssignRoomNumberAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event, Stay $stay): JsonResponse
    {
        if ($event !== $stay->getEvent()) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        $roomNumber = $data['value'] ?? '';

        $this->commandBus->handle(new AssignRoomNumber($stay, $roomNumber));

        return new JsonResponse([
            'value' => $roomNumber,
        ]);
    }
}
