<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class RoomingListAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(EngineInterface $engine, QueryBusInterface $queryBus)
    {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        $locale = $event->getAvailableLocale($request->getLocale());
        $roomingListView = $this->queryBus->handle(new ListViewQuery($event, $locale));

        return new Response($this->engine->render('@Admin/Rooming/RoomingList/list.html.twig', [
            'event' => $event,
            'roomingListView' => $roomingListView,
        ]));
    }
}
