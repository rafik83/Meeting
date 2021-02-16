<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Crisp;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\ExtraParameter\GetExtraParameterQuery;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ChatAction
{
    /** @var QueryBusInterface */
    private $queryBus;
    private Environment $engine;
    private EventByHostResolver $eventByHostResolver;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $engine,
        EventByHostResolver $eventByHostResolver
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->eventByHostResolver = $eventByHostResolver;
    }

    public function __invoke(Request $request): Response
    {
        $event = $this->eventByHostResolver->resolveEventFromHost($request->getHost());
        $extraParameter = $this->queryBus->handle(new GetExtraParameterQuery($event, Type::TYPE_CRISP_SITE_ID));
        $siteId = $extraParameter instanceof Event\ExtraParameter ? $extraParameter->getValue() : null;

        $response = new Response();

        if (null !== $siteId) {
            $response = new Response(
                $this->engine->render('@Event/Crisp/chat.html.twig', [
                    'siteId' => $siteId,
                ])
            );
        }

        $response->setMaxAge(3600);

        return $response;
    }
}
