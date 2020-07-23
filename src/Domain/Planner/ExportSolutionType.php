<?php

namespace Proximum\Vimeet\Domain\Planner;

class ExportSolutionType
{
    /**
     * This is to export a new planner file without the already planned meetings
     */
    public const SOLUTION_FROM_SCRATCH = 'from_scratch';

    /**
     * This is to export a planner file with already planned meetings
     * But these meetings can be moved and deleted by planner
     */
    public const SOLUTION_OPTIMIZE_MOVING_ALLOWED = 'moving_allowed';

    /**
     * This is to export a planner file with already planned meetings
     * But without the ability to move already planned meetings
     */
    public const SOLUTION_OPTIMIZE_LOCKED = 'locked';

    public static function getExportSolutionTypes(): array
    {
        return [
            self::SOLUTION_FROM_SCRATCH            => self::SOLUTION_FROM_SCRATCH,
            self::SOLUTION_OPTIMIZE_MOVING_ALLOWED => self::SOLUTION_OPTIMIZE_MOVING_ALLOWED,
            self::SOLUTION_OPTIMIZE_LOCKED         => self::SOLUTION_OPTIMIZE_LOCKED,
        ];
    }
}
