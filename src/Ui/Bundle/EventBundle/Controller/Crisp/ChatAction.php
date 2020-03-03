<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Crisp;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\ExtraParameter\GetExtraParameterQuery;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ChatAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
    }

    public function __invoke(Event $event): Response
    {
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
