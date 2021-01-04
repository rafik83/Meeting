<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Command\Nomenclature\Exception\MissingKeysException;
use Proximum\Vimeet\Application\Nomenclature\Import\ImporterInterface;
use Proximum\Vimeet\Domain\Event\HasSheet;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class ImportHandler
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * @var ImporterInterface
     */
    private $importer;

    /**
     * @var HasSheet
     */
    private $hasSheet;

    /**
     * ImportHandler constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param ImporterInterface               $importer
     * @param HasSheet                        $hasSheet
     */
    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        ImporterInterface $importer,
        HasSheet $hasSheet
    ) {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->importer               = $importer;
        $this->hasSheet               = $hasSheet;
    }

    /**
     * @var Import
     *
     * @throws MissingKeysException
     */
    public function handle(Import $command)
    {
        $originalKeys = $this->getKeys($command->nomenclature);
        $this->importer->import($command->nomenclature, $command->filename, $command->charset);
        $this->checkMissingKeys($originalKeys, $command->nomenclature);
        $this->nomenclatureRepository->set($command->nomenclature);
    }

    /**
     * Get last level keys
     *
     * @param Nomenclature $nomenclature
     *
     * @return array
     */
    private function getKeys(Nomenclature $nomenclature)
    {
        return array_map(function (NomenclatureItem $item) {
            return $item->getKey();
        }, $nomenclature->getLastLevel());
    }

    /**
     * If the event has sheet, we don't allow to delete existing line
     *
     * @param array        $originalKeys
     * @param Nomenclature $nomenclature
     *
     * @throws MissingKeysException
     */
    private function checkMissingKeys(array $originalKeys, Nomenclature $nomenclature)
    {
        $event = $nomenclature->getEvent();

        if (null !== $event && $this->hasSheet->on($event)) {
            $updateKeys  = $this->getKeys($nomenclature);
            $missingKeys = array_diff_key($originalKeys, $updateKeys);

            if (count($missingKeys) > 0) {
                throw new MissingKeysException($missingKeys);
            }
        }
    }
}
