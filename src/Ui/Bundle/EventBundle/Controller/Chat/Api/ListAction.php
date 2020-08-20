<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Chat\Api;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(QueryBusInterface $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function __invoke(EventDomain $eventDomain, UserDomain $userDomain, string $objectType, int $objectId)
    {
        try {
            $object = $this->queryBus->handle(
                new GuessChatMessageLinkableObject($objectType, $objectId)
            );
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), 500);
        }

        return new JsonResponse(
            [

            ]
        );
    }
}
