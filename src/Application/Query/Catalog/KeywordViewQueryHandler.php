<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Catalog\KeywordView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ForeignChar;

class KeywordViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * LocalizationViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param KeywordViewQuery $query
     *
     * @return KeywordView[]
     */
    public function handle(KeywordViewQuery $query)
    {
        $keywords = $this->sheetSearchAdapter->findKeyword(
            $query->event,
            $query->filter,
            $query->defaultFilters,
            $query->locale
        );

        $keywordViews = [];

        // handle sheetname
        if (!empty($keywords['sheet']['sheetname'])) {
            foreach ($keywords['sheet']['sheetname']['buckets'] as $keyword) {
                $keywordView = new KeywordView(ucfirst($keyword['key']));
                $keywordViews[$keywordView->id] = $keywordView;
            }
        }

        // handle keyword
        if (!empty($keywords['keywords']['keywords_aggs']['keywords_filter']['keyword'])) {
            foreach ($keywords['keywords']['keywords_aggs']['keywords_filter']['keyword']['buckets'] as $keyword) {
                $keywordView = new KeywordView($keyword['key']);
                $keywordViews[$keywordView->id] = $keywordView;
            }
        }

        $filter = $query->filter;

        $sanitizeString = function (string $name, int $length) {
            return mb_strtolower(mb_substr(ForeignChar::transliterateString($name), 0, $length));
        };

        uasort($keywordViews, function (KeywordView $one, KeywordView $another) use ($filter, $sanitizeString) {
            $filterLenght = mb_strlen($filter);
            $oneCropped = $sanitizeString($one->name, $filterLenght);
            $anotherCropped = $sanitizeString($another->name, $filterLenght);

            if ($oneCropped === $filter && $anotherCropped === $filter) {
                return mb_strlen($one->name) < mb_strlen($another->name) ? -1 : 1;
            }

            if ($oneCropped === $filter && $anotherCropped !== $filter) {
                return -1;
            }

            return 1;
        });

        return $keywordViews;
    }
}
