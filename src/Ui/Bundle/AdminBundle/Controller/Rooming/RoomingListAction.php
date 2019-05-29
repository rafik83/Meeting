<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQuery;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Filter\RoomingListFilter;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /** @var RoomingListFilter */
    private $roomingListFilter;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        RoomingListFilter $roomingListFilter,
        RouterInterface $router
    ) {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->roomingListFilter = $roomingListFilter;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        if (null !== $request->query->get('reset')) {
            $this->roomingListFilter->clear($event);

            return new RedirectResponse(
                $this->router->generate('admin_event_rooming_list', ['event' => $event->getId()])
            );
        }

        $admin = $adminDomain->getAdmin();

        if (isset($request->query->all()['filter'])) {
            $this->roomingListFilter->add($event, $request->query->all()['filter']);
        }

        $savedFilters = $this->getFilters($event, $request->getLocale(), $admin);
        $locale = $event->getAvailableLocale($request->getLocale());
        $listViewQuery = new ListViewQuery($event, $locale, $savedFilters['types'] ?? [], []);

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

    private function getFilters(Event $event, string $locale, Admin $admin)
    {
        $savedFilters = $this->roomingListFilter->get($event);

        if (null === $savedFilters) {
            return [];
        }

        $filterForm = $this->formFactory->createNamed(
            '',
            FilterType::class,
            $savedFilters,
            [
                'event' => $event,
                'locale' => $locale,
                'admin' => $admin,
                'method' => 'GET',
                'required' => false,
                'allow_extra_fields' => true,
            ]
        );

        $filterForm->submit($savedFilters);

        return $filterForm->getData();
    }
}
