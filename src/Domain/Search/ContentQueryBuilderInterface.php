<?php


namespace Proximum\Vimeet\Domain\Search;

interface ContentQueryBuilderInterface
{
    public function getQuery(string $search, string $locale): array;
}
