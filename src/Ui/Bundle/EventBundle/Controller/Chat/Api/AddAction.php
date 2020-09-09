<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Chat\Api;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Chat\AddChatMessage;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AddAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        string $objectType,
        int $objectId
    ): JsonResponse {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedHttpException();
        }

        /** @var ChatMessageLinkableInterface $object */
        $object = $this->queryBus->handle(
            new GuessChatMessageLinkableObject($objectType, $objectId)
        );

        if ($event !== $object->getEvent()) {
            throw new AccessDeniedHttpException('Object not in this event');
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing content']);
        }

        $this->commandBus->handle(new AddChatMessage($object, $user, $sheet, $data['content']));

        return new JsonResponse(['status' => 'ok']);
    }
}
