<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class NomenclatureManager
{
    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    public function create(?Event $event, string $title, array $values)
    {
        $nomenclature = new Nomenclature($title, 1, $values, true, $event);

        $this->nomenclatureRepository->add($nomenclature);
    }
}
