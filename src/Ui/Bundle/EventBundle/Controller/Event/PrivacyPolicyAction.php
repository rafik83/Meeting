<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\ExtraParameter\GetExtraParameterQuery;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class PrivacyPolicyAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        EngineInterface $engine,
        QueryBusInterface $queryBus
    ) {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(EventDomain $eventDomain): Response
    {
        $event = $eventDomain->getEvent();
        $hasTracking = $this->queryBus->handle(
                new GetExtraParameterQuery($event, Type::TYPE_TRACKING_CODE)
            ) instanceof Event\ExtraParameter;

        return new Response(
            $this->engine->render(
                '@Event/Event/privacy-policy.html.twig',
                [
                    'event' => $event,
                    'hasTracking' => $hasTracking,
                ]
            )
        );
    }
}
