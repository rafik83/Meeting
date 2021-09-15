<?php


namespace Proximum\Vimeet\Infrastructure\Elastica\QueryBuilder;

use Elastica\Query\MultiMatch;
use Proximum\Vimeet\Domain\Search\ContentQueryBuilderInterface;
use Proximum\Vimeet\Infrastructure\Elastica\AvailableLocales;

class ContentQueryBuilder implements ContentQueryBuilderInterface
{
    private const BOOSTER_SHEET_NAME      = 5;
    private const BOOSTER_LOCALE_CONTENT  = 3;
    private const BOOSTER_DEFAULT_CONTENT = 2;

    // Percentage content minimum should match
    private const CONTENT_MINIMUM_SHOULD_MATCH = 90;

    private const INITIAL_BOOSTER = 1;

    private function createQuery(string $search, string $locale): MultiMatch
    {
        // Boost sheetname and content
        $fields = [
            sprintf('sheetName^%s', self::INITIAL_BOOSTER * self::BOOSTER_SHEET_NAME),
            sprintf('content^%s', self::INITIAL_BOOSTER * self::BOOSTER_DEFAULT_CONTENT),
        ];

        if (\in_array($locale, AvailableLocales::getAvailableLocalesForContent(), true)) {
            // If locale field is available
            $fields[] = sprintf('content_%s^%s', $locale, self::INITIAL_BOOSTER * self::BOOSTER_LOCALE_CONTENT);
        }

        $multiMatch = new MultiMatch();
        $multiMatch
            ->setMinimumShouldMatch(self::CONTENT_MINIMUM_SHOULD_MATCH . '%')
            ->setFields($fields)
            ->setType(MultiMatch::TYPE_CROSS_FIELDS)
            ->setQuery($search)
        ;

        return $multiMatch;
    }

    public function getElasticaQuery(string $search, string $locale): MultiMatch
    {
        return $this->createQuery($search, $locale);
    }

    public function getQuery(string $search, string $locale): array
    {
        $query = $this->createQuery($search, $locale);

        return $query->toArray();
    }
}
