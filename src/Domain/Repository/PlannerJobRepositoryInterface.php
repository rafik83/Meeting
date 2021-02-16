<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\PlannerJob;

interface PlannerJobRepositoryInterface
{
    /**
     * @param PlannerJob $plannerJob
     */
    public function add(PlannerJob $plannerJob): void;

    /**
     * @param PlannerJob $plannerJob
     */
    public function set(PlannerJob $plannerJob): void;

    /**
     * @param Event $event
     *
     * @return null|PlannerJob
     */
    public function findLastByEvent(Event $event): ?PlannerJob;

    public function countByAdmin(Admin $admin): int;

    /**
     * @param int $id
     *
     * @return null|PlannerJob
     */
    public function getById(int $id): ?PlannerJob;

    /**
     * @param string $filename
     *
     * @return null|PlannerJob
     */
    public function findByFilename(string $filename): ?PlannerJob;
}
