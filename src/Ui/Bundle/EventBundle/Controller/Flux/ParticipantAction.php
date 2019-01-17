<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Flux;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Flux\ParticipantFluxQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ParticipantAction
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(SerializerAdapterInterface $serializer, QueryBusInterface $queryBus)
    {
        $this->serializer = $serializer;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, EventDomain $eventDomain): Response
    {
        $eventId = (int)$request->get('token');

        if ($eventId !== $eventDomain->getEvent()->getId()) {
            throw new AccessDeniedException();
        }

        $participantFlux = $this->queryBus->handle(
            new ParticipantFluxQuery(
                $eventDomain->getEvent(),
                $eventDomain->getEvent()->getAvailableLocale($request->getLocale())
            )
        );

        $responseContent = $this->serializer->serialize($participantFlux, 'xml', [
            'xml_root_node_name' => 'participants',
        ]);

        return new Response($responseContent);
    }
}
