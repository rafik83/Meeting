<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class CreateHandler
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * CreateHandler constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * @var Create
     *
     * @return CreateResult
     */
    public function handle(Create $command)
    {
        $nomenclature = new Nomenclature($command->title, 1, [], true, $command->event);

        $this->nomenclatureRepository->add($nomenclature);

        return new CreateResult($nomenclature);
    }
}
