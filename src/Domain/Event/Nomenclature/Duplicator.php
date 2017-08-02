<?php

namespace Proximum\Vimeet\Domain\Event\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
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
     * Duplicator constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param NomenclatureCloner              $nomenclatureCloner
     */
    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        NomenclatureCloner $nomenclatureCloner
    ) {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->nomenclatureCloner = $nomenclatureCloner;
    }

    /**
     * @param Event $event
     * @param array $duplicationHelper
     *
     * @return array
     */
    public function duplicate(Event $event, array $duplicationHelper)
    {
        $nomenclatures = $this->nomenclatureRepository->findByEvent($event->getDuplicatedFrom());

        foreach ($nomenclatures as $nomenclature) {
            $newNomenclature = $this->nomenclatureCloner->duplicate($nomenclature, $event);
            $duplicationHelper['nomenclature'][$nomenclature->getId()] = $newNomenclature;
            $this->nomenclatureRepository->add($newNomenclature);
        }

        return $duplicationHelper;
    }
}
