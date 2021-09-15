<?php

namespace Proximum\Vimeet\Domain\Event\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class Duplicator
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * @var NomenclatureCloner
     */
    private $nomenclatureCloner;

    /**
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param NomenclatureCloner              $nomenclatureCloner
     */
    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        NomenclatureCloner $nomenclatureCloner
    ) {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->nomenclatureCloner     = $nomenclatureCloner;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $nomenclatures = $this->nomenclatureRepository->findByEvent($event->getDuplicatedFrom());

        foreach ($nomenclatures as $nomenclature) {
            $newNomenclature = $this->nomenclatureCloner->duplicate($nomenclature, $event);
            $duplicatorDataStorage->nomenclatures[$nomenclature->getId()] = $newNomenclature;
            $this->nomenclatureRepository->add($newNomenclature);
        }

        return $duplicatorDataStorage;
    }
}
