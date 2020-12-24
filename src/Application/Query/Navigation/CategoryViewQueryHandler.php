<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Category\BillingViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\BillingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\CatalogViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\CatalogViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\FormsViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\FormsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\MemberSpaceViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\MemberSpaceViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\PackageViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\PackageViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Category\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;

class CategoryViewQueryHandler
{
    /** @var MemberSpaceViewQueryHandler */
    private $memberSpaceViewQueryHandler;

    /** @var BillingViewQueryHandler */
    private $billingViewQueryHandler;

    /** @var CatalogViewQueryHandler */
    private $catalogViewQueryHandler;

    /** @var MeetingViewQueryHandler */
    private $meetingViewQueryHandler;

    /** @var PlanningViewQueryHandler */
    private $planningViewQueryHandler;

    /** @var SheetViewQueryHandler */
    private $sheetViewQueryHandler;

    /** @var PackageViewQueryHandler */
    private $packageViewQueryHandler;

    /** @var ProgramViewQueryHandler */
    private $programViewQueryHandler;

    /** @var FormsViewQueryHandler */
    private $formsViewQueryHandler;

    public function __construct(
        MemberSpaceViewQueryHandler $memberSpaceViewQueryHandler,
        BillingViewQueryHandler $billingViewQueryHandler,
        CatalogViewQueryHandler $catalogViewQueryHandler,
        MeetingViewQueryHandler $meetingViewQueryHandler,
        PlanningViewQueryHandler $planningViewQueryHandler,
        SheetViewQueryHandler $sheetViewQueryHandler,
        PackageViewQueryHandler $packageViewQueryHandler,
        ProgramViewQueryHandler $programViewQueryHandler,
        FormsViewQueryHandler $formsViewQueryHandler
    ) {
        $this->memberSpaceViewQueryHandler = $memberSpaceViewQueryHandler;
        $this->billingViewQueryHandler     = $billingViewQueryHandler;
        $this->catalogViewQueryHandler     = $catalogViewQueryHandler;
        $this->meetingViewQueryHandler     = $meetingViewQueryHandler;
        $this->planningViewQueryHandler    = $planningViewQueryHandler;
        $this->sheetViewQueryHandler       = $sheetViewQueryHandler;
        $this->packageViewQueryHandler     = $packageViewQueryHandler;
        $this->programViewQueryHandler     = $programViewQueryHandler;
        $this->formsViewQueryHandler       = $formsViewQueryHandler;
    }

    /**
     * @param CategoryViewQuery $categoryViewQuery
     *
     * @return CategoryView|null
     */
    public function handle(CategoryViewQuery $categoryViewQuery): ?CategoryView
    {
        switch ($categoryViewQuery->categoryType) {
            case Category::MEMBER_SPACE:
                return $this->memberSpaceViewQueryHandler->handle(new MemberSpaceViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::BILLING:
                return $this->billingViewQueryHandler->handle(new BillingViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::SHEET:
                return $this->sheetViewQueryHandler->handle(new SheetViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::CATALOG:
                return $this->catalogViewQueryHandler->handle(new CatalogViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::PLANNING:
                return $this->planningViewQueryHandler->handle(new PlanningViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::MEETING:
                return $this->meetingViewQueryHandler->handle(new MeetingViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::PACKAGE:
                return $this->packageViewQueryHandler->handle(new PackageViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::PROGRAM:
                return $this->programViewQueryHandler->handle(new ProgramViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
            case Category::FORMS:
                return $this->formsViewQueryHandler->handle(new FormsViewQuery(
                    $categoryViewQuery->sheet,
                    $categoryViewQuery->user,
                    $categoryViewQuery->locale,
                    $categoryViewQuery->staticFormulation
                ));
        }

        return null;
    }
}
