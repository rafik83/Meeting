<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Api\Happening;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Happening\GetApiListQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Api\OptionsTrait;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ListAction
{
    use OptionsTrait;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        QueryBusInterface $queryBus
    ) {
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain
    ): JsonResponse {
        $participantTypes = $request->query->get('filter')['participant_type'] ?? null;
        if (null !== $participantTypes && !is_array($participantTypes)) {
            throw new BadRequestHttpException('Participant type filter: bad format');
        }

        $getApiListQuery = new GetApiListQuery(
            $eventDomain->getEvent(),
            $request->getLocale(),
            $request->getSchemeAndHttpHost(),
            $participantTypes
        );

        $response = new JsonResponse($this->queryBus->handle($getApiListQuery));
        $response->setPublic();
        $response->setMaxAge(600);
        $this->setCorsHeaders($response);

        return $response;
    }
}
