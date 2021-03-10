<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQuery;
use Proximum\Vimeet\Domain\Filter\RoomingListFilter;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        RoomingListFilter $roomingListFilter,
        RouterInterface $router,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->roomingListFilter = $roomingListFilter;
        $this->router = $router;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException();
        }

        if (null !== $request->query->get('reset')) {
            $this->roomingListFilter->clear($event);

            return new RedirectResponse(
                $this->router->generate('admin_event_rooming_list', ['event' => $event->getId()])
            );
        }

        $rawSavedFilters = $this->roomingListFilter->get($event);

        if (null !== $rawSavedFilters && !empty($rawSavedFilters) && empty($request->query->all())) {
            return new RedirectResponse(
                $this->router->generate('admin_event_rooming_list', ['event' => $event->getId()] + $rawSavedFilters)
            );
        }

        $admin = $adminDomain->getAdmin();

        if (!empty($request->query->all())) {
            $this->roomingListFilter->add($event, $request->query->all());
        }

        $locale = $event->getAvailableLocale($request->getLocale());
        $savedFilters = $this->getFilters($rawSavedFilters, $event, $request->getLocale(), $admin);
        $listViewQuery = new ListViewQuery(
            $event,
            $locale,
            $savedFilters['filter']['types'] ?? [],
            $savedFilters['filter']['states'] ?? []
        );

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

    private function getFilters(?array $rawSavedFilters, Event $event, string $locale, Admin $admin)
    {
        if (null === $rawSavedFilters) {
            return [];
        }

        $filterForm = $this->formFactory->createNamed(
            '',
            FilterType::class,
            $rawSavedFilters,
            [
                'event' => $event,
                'locale' => $locale,
                'admin' => $admin,
            ]
        );

        $filterForm->submit($rawSavedFilters);

        return $filterForm->getData();
    }
}
