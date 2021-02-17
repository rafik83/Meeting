<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureHandler;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityQueryHandler;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class CatalogController extends AbstractController
{
    private SearchFacetRepositoryInterface $searchFacetRepository;
    private CatalogTagFilterRepositoryInterface $catalogTagFilterRepository;
    private ConfigureHandler $catalogConfigureHandler;
    private EventUrlGeneratorInterface $urlGenerator;
    private CatalogVisibilityQueryHandler $catalogVisibilityQueryHandler;

    public function __construct(
        SearchFacetRepositoryInterface $searchFacetRepository,
        CatalogTagFilterRepositoryInterface $catalogTagFilterRepository,
        ConfigureHandler $catalogConfigureHandler,
        EventUrlGeneratorInterface $urlGenerator,
        CatalogVisibilityQueryHandler $catalogVisibilityQueryHandler
    ) {
        $this->searchFacetRepository = $searchFacetRepository;
        $this->catalogTagFilterRepository = $catalogTagFilterRepository;
        $this->urlGenerator = $urlGenerator;
        $this->catalogConfigureHandler = $catalogConfigureHandler;
        $this->catalogVisibilityQueryHandler = $catalogVisibilityQueryHandler;
    }

    public function configureAction(Request $request, Event $event, UserInterface $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $searchFacets = $this->searchFacetRepository->getByEvent($event);
        $catalogTagFilters = $this->catalogTagFilterRepository
            ->getByEventAndType($event, CatalogTagFilter::TYPE_EXTERNAL);

        $catalogVisibility = $this->catalogVisibilityQueryHandler
            ->handle(new CatalogVisibilityQuery($event));

        $configure = new Configure($event, $catalogVisibility, $searchFacets, $catalogTagFilters);

        $configureForm = $this->createForm(ConfigureType::class, $configure, [
            'user'   => $user,
            'event'  => $event,
            'locale' => $locale,
            'types'  => SearchFacet::getAllTypes(),
        ]);

        if ($configureForm->handleRequest($request)->isSubmitted() && $configureForm->isValid()) {
            $this->catalogConfigureHandler->handle($configure);
            $this->addFlash('success', 'flash.admin.event.catalog.external.configure.success');

            return $this->redirectToRoute('admin_event_external_catalog_configure', ['event' => $event->getId()]);
        }

        $externalCatalogUrls = [];
        foreach ($event->getLocales() as $locale) {
            $externalCatalogUrls[] = $this->urlGenerator->generateEventAbsoluteUrl(
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
