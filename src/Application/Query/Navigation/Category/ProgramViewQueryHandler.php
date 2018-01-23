<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category as HappeningCategory;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class ProgramViewQueryHandler
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * ProgramViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface   $navigationBuilder
     * @param HappeningsAccessChecker      $happeningsAccessChecker
     * @param HappeningRepositoryInterface $happeningRepository
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        HappeningsAccessChecker $happeningsAccessChecker,
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->happeningRepository     = $happeningRepository;
        $this->happeningsAccessChecker = $happeningsAccessChecker;
        $this->navigationBuilder       = $navigationBuilder;
    }

    /**
     * @param ProgramViewQuery $programViewQuery
     *
     * @return null|CategoryView
     */
    public function handle(ProgramViewQuery $programViewQuery)
    {
        if (!$this->happeningsAccessChecker->allowedToAccess($programViewQuery->sheet->getEvent())) {
            return null;
        }

        $linksView = [];

        $happenings = $this->happeningRepository->findListByEvent(
            $programViewQuery->sheet->getEvent(),
            $programViewQuery->locale
        );

        $categories = [];

        /** @var Happening $happening */
        foreach ($happenings as $happening) {
            $categories[$happening->getCategory()->getId()] = $happening->getCategory();
        }

        usort($categories, function (HappeningCategory $previousCategory, HappeningCategory $nextCategory) {
            return strcmp($previousCategory->getRank(), $nextCategory->getRank());
        });

        /** @var Happening\Category $category */
        foreach ($categories as $category) {
            $linksView[] = new LinkView(
                $category->getTitle($programViewQuery->locale),
                $this->navigationBuilder->getRoute('happening_program', ['sheet' => $programViewQuery->sheet->getId()]),
                null
            );
        }

        return new CategoryView(Category::PROGRAM, Category::PROGRAM_ICON, $linksView, true);
    }
}
