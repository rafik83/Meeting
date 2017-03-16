<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Planner;

class ExportSolutionType
{
    /**
     * This is to export a new planner file without the already planned meetings
     */
    const SOLUTION_FROM_SCRATCH    = 'from_scratch';

    /**
     * This is to export a planner file with already planned meetings
     * But these meetings can be moved and deleted by planner
     */
    const SOLUTION_OPTIMIZE_MOVING = 'moving_allowed';

    /**
     * This is to export a planner file with already planned meetings
     * But without the ability to move already planned meetings
     */
    const SOLUTION_OPTIMIZE_LOCKED = 'locked';

    /**
     * @return array
     */
    public static function getExportSolutionTypes()
    {
        return [
            self::SOLUTION_FROM_SCRATCH    => self::SOLUTION_FROM_SCRATCH,
            self::SOLUTION_OPTIMIZE_MOVING => self::SOLUTION_OPTIMIZE_MOVING,
            self::SOLUTION_OPTIMIZE_LOCKED => self::SOLUTION_OPTIMIZE_LOCKED,
        ];
    }
}
