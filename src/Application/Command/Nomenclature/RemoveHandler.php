<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Exception\Nomenclature\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Nomenclature\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class RemoveHandler
{
    /** @var RemoveAuthorizationChecker */
    private $removeAuthorizationChecker;

    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /**
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param RemoveAuthorizationChecker      $removeAuthorizationChecker
     */
    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        RemoveAuthorizationChecker $removeAuthorizationChecker
    ) {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->removeAuthorizationChecker = $removeAuthorizationChecker;
    }

    /**
     * @param Remove $command
     */
    public function handle(Remove $command)
    {
        if (!$this->removeAuthorizationChecker->canBeRemoved($command->nomenclature)) {
            throw new CanNotBeRemovedException(
                sprintf('The nomenclature %s can not be removed', $command->nomenclature->getId())
            );
        }

        $this->nomenclatureRepository->remove($command->nomenclature);
    }
}
