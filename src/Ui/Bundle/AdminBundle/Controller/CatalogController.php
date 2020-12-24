<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityQuery;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Repository\Catalog\CatalogTagFilterRepository;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class CatalogController extends Controller
{
    /**
     * @param Request       $request
     * @param Event         $event
     * @param UserInterface $user
     *
     * @return Response
     */
    public function configureAction(Request $request, Event $event, UserInterface $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $searchFacets = $this->get('vimeet_infrastructure.repository.catalog.external.search_facet')->getByEvent($event);
        $catalogTagFilters = $this->get(CatalogTagFilterRepository::class)
            ->getByEventAndType($event, CatalogTagFilter::TYPE_EXTERNAL);

        $catalogVisibility = $this
            ->get('query.catalog.external.catalog_visibility_view_query_handler')
            ->handle(new CatalogVisibilityQuery($event));

        $configure = new Configure($event, $catalogVisibility, $searchFacets, $catalogTagFilters);

        $configureForm = $this->createForm(ConfigureType::class, $configure, [
            'user'   => $user,
            'event'  => $event,
            'locale' => $locale,
            'types'  => SearchFacet::getAllTypes(),
        ]);

        if ($configureForm->handleRequest($request)->isSubmitted() && $configureForm->isValid()) {
            $this->get('catalog.external.configure_handler')->handle($configure);
            $this->addFlash('success', 'flash.admin.event.catalog.external.configure.success');

            return $this->redirectToRoute('admin_event_external_catalog_configure', ['event' => $event->getId()]);
        }

        $externalCatalogUrls = [];
        foreach ($event->getLocales() as $locale) {
            $externalCatalogUrls[] = $this->get('adapter.event_url_generator')->generateEventAbsoluteUrl(
                $event,
                'event_catalog_external_index',
                ['_locale' => $locale]
            );
        }

        return $this->render('AdminBundle:Catalog/External:configure.html.twig', [
            'event'               => $event,
            'externalCatalogUrls' => $externalCatalogUrls,
            'form'                => $configureForm->createView(),
        ]);
    }
}
