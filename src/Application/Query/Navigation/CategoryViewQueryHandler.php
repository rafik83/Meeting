<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Category\BillingViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\BillingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\CatalogViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\CatalogViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\HappeningViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\MemberSpaceViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\MemberSpaceViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\PackageViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\PackageViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\SheetViewQueryHandler;

class CategoryViewQueryHandler
{
    /**
     * @var MemberSpaceViewQueryHandler
     */
    private $memberSpaceViewQueryHandler;

    /**
     * @var BillingViewQueryHandler
     */
    private $billingViewQueryHandler;

    /**
     * @var CatalogViewQueryHandler
     */
    private $catalogViewQueryHandler;

    /**
     * @var HappeningViewQueryHandler
     */
    private $happeningViewQueryHandler;

    /**
     * @var PlanningViewQueryHandler
     */
    private $planningViewQueryHandler;

    /**
     * @var SheetViewQueryHandler
     */
    private $sheetViewQueryHandler;

    /**
     * @var PackageViewQueryHandler
     */
    private $packageViewQueryHandler;

    /**
     * CategoryViewQueryHandler constructor.
     *
     * @param MemberSpaceViewQueryHandler $memberSpaceViewQueryHandler
     * @param BillingViewQueryHandler     $billingViewQueryHandler
     * @param CatalogViewQueryHandler     $catalogViewQueryHandler
     * @param HappeningViewQueryHandler   $happeningViewQueryHandler
     * @param PlanningViewQueryHandler    $planningViewQueryHandler
     * @param SheetViewQueryHandler       $sheetViewQueryHandler
     * @param PackageViewQueryHandler     $packageViewQueryHandler
     */
    public function __construct(
        MemberSpaceViewQueryHandler $memberSpaceViewQueryHandler,
        BillingViewQueryHandler $billingViewQueryHandler,
        CatalogViewQueryHandler $catalogViewQueryHandler,
        HappeningViewQueryHandler $happeningViewQueryHandler,
        PlanningViewQueryHandler $planningViewQueryHandler,
        SheetViewQueryHandler $sheetViewQueryHandler,
        PackageViewQueryHandler $packageViewQueryHandler
    ) {
        $this->memberSpaceViewQueryHandler = $memberSpaceViewQueryHandler;
        $this->billingViewQueryHandler     = $billingViewQueryHandler;
        $this->catalogViewQueryHandler     = $catalogViewQueryHandler;
        $this->happeningViewQueryHandler   = $happeningViewQueryHandler;
        $this->planningViewQueryHandler    = $planningViewQueryHandler;
        $this->sheetViewQueryHandler       = $sheetViewQueryHandler;
        $this->packageViewQueryHandler     = $packageViewQueryHandler;
    }

    /**
     * @param CategoryViewQuery $categoryViewQuery
     *
     * @return \Proximum\Vimeet\Application\View\Navigation\CategoryView
     */
    public function handle(CategoryViewQuery $categoryViewQuery)
    {
        switch ($categoryViewQuery->categoryType) {
            case Category::MEMBER_SPACE:
                return $this->memberSpaceViewQueryHandler->handle(new MemberSpaceViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale
                ));
                break;
            case Category::BILLING:
                return $this->billingViewQueryHandler->handle(new BillingViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale
                ));
                break;
            case Category::SHEET:
                return $this->sheetViewQueryHandler->handle(new SheetViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale
                ));
                break;
            case Category::CATALOG:
                return $this->catalogViewQueryHandler->handle(new CatalogViewQuery());
                break;
            case Category::PLANNING:
                return $this->planningViewQueryHandler->handle(new PlanningViewQuery());
                break;
            case Category::HAPPENING:
                return $this->happeningViewQueryHandler->handle(new HappeningViewQuery());
                break;
            case Category::PACKAGE:
                return $this->packageViewQueryHandler->handle(new PackageViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale
                ));
                break;
        }
    }
}
