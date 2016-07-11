<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class PackageViewQueryHandler
{
    /**
     * @var FunnelFactory
     */
    private $funnelFactory;

    /**
     * @var NavigationBuilder
     */
    private $navigationBuilder;

    /**
     * PackageViewQueryHandler constructor.
     *
     * @param FunnelFactory     $funnelFactory
     * @param NavigationBuilder $navigationBuilder
     */
    public function __construct(FunnelFactory $funnelFactory, NavigationBuilder $navigationBuilder)
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
        if (!$packageQuery->sheet->getPackage()->isPassable()) {
            return null;
        }

        $linksView = [];
        $funnel    = $this->funnelFactory->create($packageQuery->sheet, $packageQuery->locale);

        foreach ($funnel->getSteps() as $step) {
            if ($step->completed === true || $step->index === 1) {
                $linksView[] = new LinkView(
                    $step->title,
                    $this->navigationBuilder->getRoute('event_package_step', [
                        'sheet' => $packageQuery->sheet->getId(),
                        'step'  => $step->index,
                    ])
                );
            }
        }

        if ($funnel->isCompleted()) {
            $linksView[] = new LinkView(
                'navigation.links.package.summary',
                $this->navigationBuilder->getRoute('event_package_summary', [
                    'sheet' => $packageQuery->sheet->getId(),
                ])
            );
        }

        return new CategoryView(Category::PACKAGE, Category::PACKAGE_ICON, $linksView);
    }
}
