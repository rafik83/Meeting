<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Type;

interface BadgeRepositoryInterface
{
    public function findByType(Type $type): ?Badge;

    public function add(Badge $badge): void;

    public function set(Badge $badge): void;
}
