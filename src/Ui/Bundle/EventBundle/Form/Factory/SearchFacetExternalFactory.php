<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Factory;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\FilteredFieldsQuery;
use Proximum\Vimeet\Application\Query\Catalog\NomenclatureTagViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\View\Catalog\FilteredFieldsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Domain\Exception\Catalog\CatalogVisibilityNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchExternalType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SearchFacetExternalFactory
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var CommandBus */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var array of TypeView[] indexed by Event id */
    private $typeViewsByEvent;

    /** @var CategoryView[] indexed by Event id */
    private $categoryViewsByEvent;

    public function __construct(
        CommandBus $commandBus,
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @param Event            $event
     * @param string           $locale
     * @param array            $filters
     * @param SearchFacetsView $searchFacetsView
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return FormInterface
     */
    public function create(Event $event, string $locale, array $filters, SearchFacetsView $searchFacetsView): FormInterface
    {
        $initialFieldsView = $this->getInitialFieldsView($event, $locale, $searchFacetsView);

        return $this->getForm(
            $event,
            $locale,
            $filters,
            $initialFieldsView->catalogFilterViewsResult
        );
    }

    /**
     * @param Event            $event
     * @param string           $locale
     * @param array            $filters
     * @param array            $currentAggregations
     * @param SearchFacetsView $searchFacetsView
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return FormInterface
     */
    public function createFiltered(
        Event $event,
        string $locale,
        array $filters,
        array $currentAggregations,
        SearchFacetsView $searchFacetsView
    ): FormInterface {
        $initialFieldsView = $this->getInitialFieldsView($event, $locale, $searchFacetsView);

        /** @var FilteredFieldsView $filteredFieldsView */
        $filteredFieldsView = $this->commandBus->handle(
            new FilteredFieldsQuery(
                $event,
                $filters,
                $currentAggregations,
                $initialFieldsView->catalogFilterViewsResult,
                $locale
            )
        );

        return $this->getForm(
            $event,
            $locale,
            $filters,
            $filteredFieldsView->catalogFilterViewsResult
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return TypeView[]
     */
    public function getTypeViews(Event $event, string $locale): array
    {
        if (!isset($this->typeViewsByEvent[$event->getId()])) {
            $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

            if (null === $catalogVisibility) {
                throw new CatalogVisibilityNotFoundException();
            }

            $this->typeViewsByEvent[$event->getId()] = $this->commandBus->handle(
                new TypeViewQuery($event, $catalogVisibility->getTypes(), $locale)
            );
        }

        return $this->typeViewsByEvent[$event->getId()];
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return CategoryView[]
     */
    public function getCategoryViews(Event $event, string $locale): array
    {
        if (!isset($this->categoryViewsByEvent[$event->getId()])) {
            $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

            if (null === $catalogVisibility) {
                throw new CatalogVisibilityNotFoundException();
            }

            $this->categoryViewsByEvent[$event->getId()] = $this->commandBus->handle(
                new CategoryViewQuery($event, $catalogVisibility->getCategories(), $locale)
            );
        }

        return $this->categoryViewsByEvent[$event->getId()];
    }

    /**
     * @throws CatalogVisibilityNotFoundException
     */
    private function getInitialFieldsView(Event $event, string $locale, SearchFacetsView $searchFacetsView): FilteredFieldsView
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        if (null === $catalogVisibility) {
            throw new CatalogVisibilityNotFoundException();
        }

        $typeViews = $this->getTypeViews($event, $locale);
        $categoryViews = $this->getCategoryViews($event, $locale);

        $organizationCategoryViews = $this->commandBus->handle(
            new OrganizationCategoryViewQuery($event, $locale)
        );

        $positionViews = $this->commandBus->handle(
            new PositionViewQuery($event, $locale)
        );

        $taggedNomenclatureTagViews = [];
        $tagFilterViews = $searchFacetsView->getTagFilterViews();

        if (!empty($tagFilterViews)) {
            $taggedNomenclatureTagViews = $this->commandBus->handle(
                new NomenclatureTagViewQuery($event, array_keys($tagFilterViews), $locale)
            );
        }

        return new FilteredFieldsView(
            new CatalogFilterViewsResult(
                CatalogFilterViewsResult::RESULT_CATEGORY_OR_TYPE,
                [],
                $categoryViews,
                $typeViews,
                $organizationCategoryViews,
                $positionViews,
                $taggedNomenclatureTagViews
            )
        );
    }

    private function getForm(
        Event $event,
        string $locale,
        array $filters = [],
        CatalogFilterViewsResult $catalogFilterViewsResult
    ): FormInterface {
        return $this->formFactory->createNamed('', SearchExternalType::class, $filters, [
            'action' => $this->router->generate('event_catalog_external_index'),
            'typeViews' => $catalogFilterViewsResult->typeViews,
            'categoryViews' => $catalogFilterViewsResult->categoryViews,
            'organizationCategoryViews' => $catalogFilterViewsResult->organizationCategoryViews,
            'positionViews' => $catalogFilterViewsResult->positionViews,
            'taggedNomenclatureTagViews' => $catalogFilterViewsResult->taggedNomenclatureTagViews,
            'event' => $event,
            'locale' => $locale,
        ]);
    }
}
