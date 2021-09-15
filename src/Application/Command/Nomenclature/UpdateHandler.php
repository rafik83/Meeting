<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class UpdateHandler
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * @var Update
     */
    public function handle(Update $command)
    {
        $nomenclature = $command->sort ? $command->nomenclature->enableSort() : $command->nomenclature->disableSort();
        $nomenclature->setTitle($command->title);

        $this->nomenclatureRepository->set($nomenclature);
    }
}
