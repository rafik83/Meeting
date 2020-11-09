<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\SearchFacet\Update;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Repository\Catalog\CatalogTagFilterRepository;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchFacetController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $searchFacets = $this->get('vimeet_infrastructure.repository.search_facet')->getByEvent($event);
        $catalogTagFilters = $this->get(CatalogTagFilterRepository::class)
            ->getByEventAndType($event, CatalogTagFilter::TYPE_INTERNAL);

        $types = SearchFacet::getAllTypes();
        $command = new Update($event, $searchFacets, $catalogTagFilters);
        $form = $this->createForm(UpdateType::class, $command, [
            'submit' => true,
            'types' => $types,
            'event' => $event,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
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
