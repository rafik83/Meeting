<?php

namespace Proximum\Vimeet\Infrastructure\Elastica\QueryBuilder;

use Elastica\Query\BoolQuery;
use Elastica\Query\Nested;
use Elastica\Query\Term;
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class NomenclatureQueryBuilder
{
    /**
     * @var array
     */
    private $nomenclatureItems;

    /**
     * @var Nested
     */
    private $query;

    /**
     * NomenclatureQueryBuilder constructor.
     *
     * @param array $nomenclatureItems
     */
    public function __construct(array $nomenclatureItems)
    {
        $this->query             = new Nested();
        $this->nomenclatureItems = $nomenclatureItems;
    }

    /**
     * @throws NomenclatureNotFoundException
     *
     * @return NomenclatureQueryBuilder
     */
    public function filterByNeed()
    {
        if (!isset($this->nomenclatureItems[Nomenclature::OBJECTIVE_NEED])) {
            throw new NomenclatureNotFoundException('Nomenclature was not found in nomenclature items array');
        }

        $boolQuery = new BoolQuery();
        $this->query->setPath('nomenclatureItemsSupply');

        foreach ($this->nomenclatureItems[Nomenclature::OBJECTIVE_NEED] as $nomenclatureItem) {
            $term = new Term(['nomenclatureItemsSupply.key' => $nomenclatureItem]);
            $boolQuery->addShould($term);
        }

        $this->query->setQuery($boolQuery);

        return $this;
    }

    /**
     * @throws NomenclatureNotFoundException
     *
     * @return NomenclatureQueryBuilder
     */
    public function filterBySupply()
    {
        if (!isset($this->nomenclatureItems[Nomenclature::OBJECTIVE_SUPPLY])) {
            throw new NomenclatureNotFoundException('Nomenclature was not found in nomenclature items array');
        }

        $boolQuery = new BoolQuery();
        $this->query->setPath('nomenclatureItemsNeeds');

        foreach ($this->nomenclatureItems[Nomenclature::OBJECTIVE_SUPPLY] as $nomenclatureItem) {
            $term = new Term(['nomenclatureItemsNeeds.key' => $nomenclatureItem]);
            $boolQuery->addShould($term);
        }

        $this->query->setQuery($boolQuery);

        return $this;
    }

    /**
     * @throws NomenclatureNotFoundException
     *
     * @return BoolQuery
     */
    public function filterBySupplyOrder()
    {
        if (!isset($this->nomenclatureItems[Nomenclature::OBJECTIVE_NEED]) ||
            !isset($this->nomenclatureItems[Nomenclature::OBJECTIVE_SUPPLY])
        ) {
            throw new NomenclatureNotFoundException('Nomenclature was not found in nomenclature items array');
        }

        $boolQuery = new BoolQuery();

        $nestedSupply = new Nested();
        $nestedSupply->setPath('nomenclatureItemsNeeds');
        $supplyBoolQuery = new BoolQuery();

        foreach ($this->nomenclatureItems[Nomenclature::OBJECTIVE_SUPPLY] as $nomenclatureItem) {
            $term = new Term(['nomenclatureItemsNeeds.key' => $nomenclatureItem]);
            $supplyBoolQuery->addShould($term);
        }

        $nestedSupply->setQuery($supplyBoolQuery);

        $nestedNeed = new Nested();
        $nestedNeed->setPath('nomenclatureItemsSupply');
        $needBoolQuery = new BoolQuery();

        foreach ($this->nomenclatureItems[Nomenclature::OBJECTIVE_NEED] as $nomenclatureItem) {
            $term = new Term(['nomenclatureItemsSupply.key' => $nomenclatureItem]);
            $needBoolQuery->addShould($term);
        }

        $nestedNeed->setQuery($needBoolQuery);

        $boolQuery
            ->addShould($nestedSupply)
            ->addShould($nestedNeed);

        return $boolQuery;
    }

    /**
     * @return Nested
     */
    public function getQuery()
    {
        return $this->query;
    }
}
