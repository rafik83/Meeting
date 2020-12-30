<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Application\View\Catalog\Aggregat\NomenclatureTagViews;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\HttpFoundation\Response;

class CatalogFilterViewsResult
{
    public const EMPTY_CATEGORY_OR_TYPE = 'empty_category_or_type';
    public const RESULT_CATEGORY_OR_TYPE = 'result_category_or_type';

    /** @var string */
    public $type;

    /** @var SheetVisitViews[] */
    public $sheetVisitViews;

    /** @var CategoryView[] */
    public $categoryViews;

    /** @var TypeView[] */
    public $typeViews;

    /** @var OrganizationCategoryView[] */
    public $organizationCategoryViews;

    /** @var PositionView[] */
    public $positionViews;

    /** @var Response */
    public $response;

    /** @var NomenclatureTagViews[] indexed by Tag */
    public $taggedNomenclatureTagViews;

    /** @var array */
    public $objectiveFilters;

    /**
     * @param string                     $type
     * @param SheetVisitView[]           $sheetVisitViews
     * @param CategoryView[]             $categoryViews
     * @param TypeView[]                 $typeViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[]             $positionViews
     * @param NomenclatureTagViews[]     $taggedNomenclatureTagViews
     * @param Response|null              $response
     * @param string[]                   $objectiveFilters
     */
    public function __construct(
        string $type,
        array $sheetVisitViews = [],
        array $categoryViews = [],
        array $typeViews = [],
        array $organizationCategoryViews = [],
        array $positionViews = [],
        array $taggedNomenclatureTagViews = [],
        Response $response = null,
        array $objectiveFilters = []
    ) {
        $this->type = $type;
        $this->sheetVisitViews = $sheetVisitViews;
        $this->categoryViews = $categoryViews;
        $this->typeViews = $typeViews;
        $this->response = $response;
        $this->organizationCategoryViews = $organizationCategoryViews;
        $this->positionViews = $positionViews;
        $this->taggedNomenclatureTagViews = $taggedNomenclatureTagViews;
        $this->objectiveFilters = $objectiveFilters;
    }

    /**
     * @return bool
     */
    public function hasEmptyCategoryOrType(): bool
    {
        return self::EMPTY_CATEGORY_OR_TYPE === $this->type;
    }
}
