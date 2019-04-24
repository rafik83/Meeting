<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class RoomingListAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(EngineInterface $engine, QueryBusInterface $queryBus, FormFactoryInterface $formFactory)
    {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        $locale = $event->getAvailableLocale($request->getLocale());
        $listViewQuery = new ListViewQuery($event, $locale, []);

        $form = $this->formFactory->create(FilterType::class, $listViewQuery, [
            'event' => $event, 'locale' => $locale, 'admin' => $adminDomain->getAdmin(),
        ]);

        $form->handleRequest($request);

        $roomingListView = $this->queryBus->handle($listViewQuery);

        return new Response($this->engine->render('@Admin/Rooming/RoomingList/list.html.twig', [
            'event' => $event,
            'roomingListView' => $roomingListView,
            'form' => $form->createView(),
        ]));
    }
}
