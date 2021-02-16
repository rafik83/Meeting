<?php

namespace Proximum\Vimeet\Application\Query\Nomenclature;

use Proximum\Vimeet\Domain\Nomenclature\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

abstract class AbstractNomenclatureViewQueryHandler
{
    /** @var NomenclatureRepositoryInterface */
    protected $nomenclatureRepository;

    /** @var RemoveAuthorizationChecker */
    protected $removeAuthorizationChecker;

    /**
     * EventNomenclatureViewQueryHandler constructor.
     *
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
}
