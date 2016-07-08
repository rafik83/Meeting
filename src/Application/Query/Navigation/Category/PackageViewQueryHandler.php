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

class PackageViewQueryHandler
{
    /**
     * @var FunnelFactory
     */
    private $funnelFactory;

    /**
     * PackageViewQueryHandler constructor.
     *
     * @param FunnelFactory $funnelFactory
     */
    public function __construct(FunnelFactory $funnelFactory)
    {
        $this->funnelFactory = $funnelFactory;
    }

    /**
     * @param PackageViewQuery $packageQuery
     *
     * @return null|CategoryView
     */
    public function handle(PackageViewQuery $packageQuery)
    {
        if (!$packageQuery->sheet->getPackage()->isPassable()) {
            return null;
        }

        $linksView = [];
        $funnel    = $this->funnelFactory->create($packageQuery->sheet, $packageQuery->locale);

        foreach ($funnel->getSteps() as $step) {
            $linksView[] = new LinkView(
                $step->title,
                ''
            );
        }

        if ($funnel->isCompleted()) {
            $linksView[] = new LinkView(
                'navigation.links.package.summary',
                ''
            );
        }

        return new CategoryView(
            Category::PACKAGE,
            Category::PACKAGE_ICON,
            $linksView
        );
    }
}
