<?php

namespace Proximum\Vimeet\Application\Components\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningPermissionManager
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var array
     */
    private $allowedToBeModified = [];

    /**
     * HappeningPermissionManager constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     */
    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param array      $happenings
     * @param bool|false $force
     *
     * @return HappeningPermissionManager
     */
    public function loadAllowedToBeModified(array $happenings = [], $force = false)
    {
        $ids  = array_map(function (Happening $happening) { return $happening->getId(); }, $happenings);
        $diff = $force ? $ids : array_diff($ids, array_keys($this->allowedToBeModified));

        $allowedToBeModified = empty($diff) ? [] : $this->happeningRepository->findIdsWithoutParticipation($diff);

        foreach ($diff as $id) {
            $this->allowedToBeModified[$id] = in_array($id, $allowedToBeModified);
        }

        return $this;
    }

    /**
     * @param Happening  $happening
     * @param bool|false $force
     *
     * @return bool
     */
    public function isAllowedToBeModified(Happening $happening, $force = false)
    {
        $this->loadAllowedToBeModified([$happening], $force);

        return in_array($happening->getId(), $this->allowedToBeModified);
    }
}
