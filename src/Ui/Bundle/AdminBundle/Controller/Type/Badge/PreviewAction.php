<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetExampleBadgeByEventQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class PreviewAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Event $event, Type $type): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $type->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        return new Response(
            $this->engine->render(
                'EventBundle:Badge:show.html.twig',
                [
                    'event' => $event,
                    'sheet' => null,
                    'userBadgeByEventView' => $this->queryBus->handle(
                        new GetExampleBadgeByEventQuery($event, $type)
                    ),
                    'disabledNavigationMenu' => true,
                ]
            )
        );
    }
}
