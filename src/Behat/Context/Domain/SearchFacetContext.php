<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use InvalidArgumentException;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class SearchFacetContext implements Context
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /** @var StorageInterface */
    private $storage;

    public function __construct(StorageInterface $storage, SearchFacetRepositoryInterface $searchFacetRepository)
    {
        $this->storage = $storage;
        $this->searchFacetRepository = $searchFacetRepository;
    }

    /**
     * @Given there is a :type search facet
     */
    public function thereIsASearchFacet(string $type): void
    {
        $event = $this->storage->get('event');
        if (null === $event) {
            throw new InvalidArgumentException('Missing Event');
        }

        $newSearchFacet = new SearchFacet($event, $type, true);
        $newSearchFacet->translate('fr', $type, $type.' placeholder');

        $this->searchFacetRepository->add($newSearchFacet);
    }
}
