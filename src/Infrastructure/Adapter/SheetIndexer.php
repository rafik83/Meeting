<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetIndexer implements SheetIndexerInterface
{
    /**
     * @var ObjectPersister
     */
    private $persister;

    /**
     * @param ObjectPersister $persister
     */
    public function __construct(ObjectPersister $persister)
    {
        $this->persister = $persister;
    }

    /**
     * @param Sheet[] $sheets
     */
    public function reindexSheets(array $sheets): void
    {
        if (!empty($sheets)) {
            $this->persister->insertMany($sheets);
        }
    }

    /**
     * @param Sheet[] $sheets
     */
    public function updateSheets(array $sheets): void
    {
        if (!empty($sheets)) {
            $this->persister->replaceMany($sheets);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSheets(array $sheetIds): void
    {
        if (!empty($sheetIds)) {
            $this->persister->deleteManyByIdentifiers($sheetIds);
        }
    }
}
