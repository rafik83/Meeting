<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use DateTimeInterface;
use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Sheet\Catalog\CanSeeOtherSheets;

class CatalogViewQueryHandler
{
    /** @var DateTimeInterface */
    private $dateTime;

    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var CanSeeOtherSheets */
    private $canSeeOtherSheets;

    /**
     * @param DateTimeInterface          $dateTime
     * @param NavigationBuilderInterface $navigationBuilder
     * @param CanSeeOtherSheets          $canSeeOtherSheets
     */
    public function __construct(
        DateTimeInterface $dateTime,
        NavigationBuilderInterface $navigationBuilder,
        CanSeeOtherSheets $canSeeOtherSheets
    ) {
        $this->dateTime = $dateTime;
        $this->navigationBuilder = $navigationBuilder;
        $this->canSeeOtherSheets = $canSeeOtherSheets;
    }

    /**
     * @param CatalogViewQuery $catalogViewQuery
     *
     * @return CategoryView|null
     */
    public function handle(CatalogViewQuery $catalogViewQuery)
    {
        if (false === $this->canSeeOtherSheets->isSatisfiedBy($catalogViewQuery->sheet)) {
            return null;
        }

        $catalogOnlineDate = $catalogViewQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getCatalogOnlineDate();


        $categoryTitle = Category::CATALOG;

        if (null !== $catalogViewQuery->staticFormulation) {
            $categoryTitle = $catalogViewQuery->staticFormulation->getTitle($catalogViewQuery->locale);
        }

        return new CategoryView(
            $categoryTitle,
            Category::CATALOG_ICON,
            [
                $this->getLinkView(
                    $catalogViewQuery->sheet,
                    $catalogViewQuery->locale,
                    $catalogOnlineDate
                ),
            ],
            true
        );
    }

    /**
     * @param Sheet              $sheet
     * @param string             $locale
     * @param \DateTimeInterface $catalogOnlineDate
     *
     * @return LinkView
     */
    private function getLinkView(Sheet $sheet, $locale, \DateTimeInterface $catalogOnlineDate = null)
    {
        if (null === $catalogOnlineDate) {
            // No date for catalog opening
            return new LinkView('navigation.links.catalog.incoming');
        }

        $formattedDate = $this->getFormattedDate($catalogOnlineDate, $locale);

        if (null !== $catalogOnlineDate && $this->dateTime > $catalogOnlineDate) {
            if (!$sheet->isInCatalog()) {
                // catalog opened but sheet not in catalog
                return new LinkView('navigation.links.catalog.sheet_not_in_catalog');
            }

            // catalog opened and sheet is in catalog
            return new LinkView(
                'navigation.links.catalog.available_date',
                $this->navigationBuilder->getRoute('event_catalog_index', ['sheet' => $sheet->getId()]),
                null,
                new StateButtonView(true, $formattedDate)
            );
        }

        // catalog not opened
        return new LinkView(
            'navigation.links.catalog.available_date',
            null,
            null,
            new StateButtonView(true, $formattedDate)
        );
    }

    /**
     * @param DateTimeInterface $date
     * @param string            $locale
     *
     * @return bool|string
     */
    private function getFormattedDate(\DateTimeInterface $date, $locale)
    {
        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG
        );
        $formatter->setPattern('d MMMM Y');
        $catalogOnlineDateFormatted = $formatter->format($date);

        return $catalogOnlineDateFormatted ? $catalogOnlineDateFormatted : '';
    }
}
