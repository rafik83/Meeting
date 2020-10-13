<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Chat\Api;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Chat\ResetSessionUnreadMessages;
use Proximum\Vimeet\Application\Command\Participant\UpdateNetworkingChatViewedAt;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Application\Query\Chat\ListChatMessages;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
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
            throw new AccessDeniedException();
        }

        /** @var ChatMessageLinkableInterface $object */
        $object = $this->queryBus->handle(
            new GuessChatMessageLinkableObject($objectType, $objectId)
        );

        if ($event !== $object->getEvent()) {
            throw new AccessDeniedException('Object not in this event');
        }

        $chatMessageViews = $this->queryBus->handle(
            new ListChatMessages($object, $user, $request->getLocale())
        );

        if ($object->getObjectType() === ChatMessage::TYPE_PRIVATE_CHAT) {
            $this->commandBus->handle(new ResetSessionUnreadMessages($object, $user));
        }

        if ($object->getObjectType() === ChatMessage::TYPE_NETWORKING) {
            $this->commandBus->handle(new UpdateNetworkingChatViewedAt($sheet, $user));
        }

        return new JsonResponse($chatMessageViews);
    }
}
