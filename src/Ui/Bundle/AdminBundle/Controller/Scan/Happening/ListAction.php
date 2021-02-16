<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Scan\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Scan\Happening\ListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        QueryBusInterface $queryBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_HOST')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$event->isAccessControlEnabled()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $list = $this->queryBus->handle(new ListViewQuery($event, $request->getLocale()));

        return $this->engine->renderResponse('AdminBundle:Scan/Happening:list.html.twig', [
            'event' => $event,
            'list' => $list,
        ]);
    }
}
