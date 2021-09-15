<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;

class PackageViewQueryHandler
{
    /** @var FunnelFactory */
    private $funnelFactory;

    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /**
     * @param FunnelFactory              $funnelFactory
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(FunnelFactory $funnelFactory, NavigationBuilderInterface $navigationBuilder)
    {
        $this->funnelFactory     = $funnelFactory;
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param PackageViewQuery $packageQuery
     *
     * @return CategoryView|null
     */
    public function handle(PackageViewQuery $packageQuery)
    {
        if (null === $packageQuery->sheet->getPackage() || !$packageQuery->sheet->getPackage()->isPassable()) {
            return null;
        }

        $linksView = [];

        if ($packageQuery->sheet->hasOrders()) {
            $linksView[] = new LinkView(
                'navigation.links.package.order_list',
                $this->navigationBuilder->getRoute('event_order_list', [
                    'sheet' => $packageQuery->sheet->getId(),
                ])
            );

            if ($packageQuery->sheet->hasNotCancelledOrders()) {
                $linksView[] = new LinkView(
                    'navigation.links.package.order_summary_total',
                    $this->navigationBuilder->getRoute(
                        'event_order_summary_total',
                        [
                            'sheet' => $packageQuery->sheet->getId(),
                        ]
                    )
                );
            }
        } else {
            $funnel = $this->funnelFactory->create($packageQuery->sheet, $packageQuery->locale);

            foreach ($funnel->getSteps() as $step) {
                $linksView[] = new LinkView(
                    $step->title,
                    $this->navigationBuilder->getRoute('event_package_step', [
                        'sheet' => $packageQuery->sheet->getId(),
                        'step'  => $step->index,
                    ]),
                    null,
                    null,
                    ($step->completed || 1 === $step->index)
                );
            }

            if ($funnel->isCompleted()) {
                $linksView[] = new LinkView(
                    'navigation.links.package.summary',
                    $this->navigationBuilder->getRoute('event_package_summary', [
                        'sheet' => $packageQuery->sheet->getId(),
                    ])
                );
            }
        }

        $categoryTitle = Category::PACKAGE;

        if (null !== $packageQuery->staticFormulation) {
            $categoryTitle = $packageQuery->staticFormulation->getTitle($packageQuery->locale);
        }

        return new CategoryView($categoryTitle, Category::PACKAGE_ICON, $linksView, false);
    }
}
