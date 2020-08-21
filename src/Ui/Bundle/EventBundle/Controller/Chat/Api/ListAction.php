<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Chat\Api;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Application\Query\Chat\ListChatMessages;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(QueryBusInterface $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        string $objectType,
        int $objectId
    ): JsonResponse {
        try {
            /** @var ChatMessageLinkableInterface $object */
            $object = $this->queryBus->handle(
                new GuessChatMessageLinkableObject($objectType, $objectId)
            );
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if ($event !== $object->getEvent()) {
            throw new AccessDeniedException('Object not in this event');
        }

        $chatMessageViews = $this->queryBus->handle(
            new ListChatMessages($object, $user, $request->getLocale())
        );

        return new JsonResponse($chatMessageViews);
    }
}
