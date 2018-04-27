<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\HttpFoundation\Response;

class CategoryTypeOrganizationAndPositionViewsResult
{
    const EMPTY_CATEGORY_OR_TYPE = 'empty_category_or_type';
    const RESULT_CATEGORY_OR_TYPE = 'result_category_or_type';

    /** @var string */
    public $type;

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

    /**
     * @param string                     $type
     * @param CategoryView[]             $categoryViews
     * @param TypeView[]                 $typeViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[]             $positionViews
     * @param Response|null              $response
     */
    public function __construct(
        string $type,
        array $categoryViews = [],
        array $typeViews = [],
        array $organizationCategoryViews = [],
        array $positionViews = [],
        Response $response = null
    ) {
        $this->type = $type;
        $this->categoryViews = $categoryViews;
        $this->typeViews = $typeViews;
        $this->response = $response;
        $this->organizationCategoryViews = $organizationCategoryViews;
        $this->positionViews = $positionViews;
    }

    /**
     * @return bool
     */
    public function hasEmptyCategoryOrType(): bool
    {
        return self::EMPTY_CATEGORY_OR_TYPE === $this->type;
    }
}
