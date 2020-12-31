<?php

namespace Proximum\Vimeet\Application\Nomenclature;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class NomenclatureCloner
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * NomenclatureCloner constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * @param Nomenclature $nomenclature
     * @param Event        $event
     *
     * @return Nomenclature
     */
    public function duplicate(Nomenclature $nomenclature, Event $event)
    {
        $clone = new Nomenclature(
            $nomenclature->getTitle(),
            $nomenclature->getDepth(),
            $nomenclature->getValue(),
            $nomenclature->isSorted(),
            $event,
            $nomenclature
        );

        $this->nomenclatureRepository->add($clone);

        return $clone;
    }

    /**
     * @param Nomenclature $nomenclature
     * @param Event        $event
     *
     * @return Nomenclature
     */
    public function duplicateIfNotExists(Nomenclature $nomenclature, Event $event)
    {
        $clone = $this->nomenclatureRepository->findClone($nomenclature, $event);

        if (null === $clone) {
            $clone = $this->duplicate($nomenclature, $event);
        }

        return $clone;
    }
}
