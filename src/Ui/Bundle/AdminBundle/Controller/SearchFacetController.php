<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\SearchFacet\Update;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchFacetController extends AbstractController
{
    private SearchFacetRepositoryInterface $searchFacetRepository;
    private CatalogTagFilterRepositoryInterface $catalogTagFilterRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        SearchFacetRepositoryInterface $searchFacetRepository,
        CatalogTagFilterRepositoryInterface $catalogTagFilterRepository,
        CommandBusInterface $commandBus) {
        $this->searchFacetRepository = $searchFacetRepository;
        $this->catalogTagFilterRepository = $catalogTagFilterRepository;
        $this->commandBus = $commandBus;
    }

    public function updateAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $searchFacets = $this->searchFacetRepository->getByEvent($event);
        $catalogTagFilters = $this->catalogTagFilterRepository
            ->getByEventAndType($event, CatalogTagFilter::TYPE_INTERNAL);

        $types = SearchFacet::getAllTypes();
        $command = new Update($event, $searchFacets, $catalogTagFilters);
        $form = $this->createForm(UpdateType::class, $command, [
            'submit' => true,
            'types' => $types,
            'event' => $event,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->addFlash('success', 'flash.admin.event.filter_facet.update.success');

            $submittedSearchFacets = $form->get('searchFacets')->getData();
            if ($submittedSearchFacets['type']['enabled'] && $submittedSearchFacets['category']['enabled']) {
                $this->addFlash('warning', 'flash.admin.event.filter_facet.update.warning_bad_config');
            }

            return $this->redirectToRoute('admin_event_search_facets', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Event/SearchFacet:update.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
