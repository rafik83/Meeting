<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AddComment;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CommentAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $comment = $data['value'] ?? '';

        $this->commandBus->handle(new AddComment($event, $user, $comment));

        return new JsonResponse([
            'value' => $comment,
        ]);
    }
}
