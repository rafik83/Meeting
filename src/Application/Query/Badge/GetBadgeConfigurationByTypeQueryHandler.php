<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;

class GetBadgeConfigurationByTypeQueryHandler
{
    /** @var BadgeRepositoryInterface */
    private $badgeRepository;

    public function __construct(BadgeRepositoryInterface $badgeRepository)
    {
        $this->badgeRepository = $badgeRepository;
    }

    public function handle(GetBadgeConfigurationByTypeQuery $query): Badge
    {
        $badge = $this->badgeRepository->findByType($query->type);

        if ($badge instanceof Badge) {
            return $badge;
        }

        return Badge::createDefault($query->type->getEvent(), $query->type);
    }
}
