<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\User\Agenda\Version;

/**
 * This class is used to determine if there is a diff in the current version and the last save version
 */
class DiffChecker
{
    /**
     * @param Version $lastUserVersion
     * @param array   $currentVersion
     *
     * @return bool
     */
    public function hasDiff(Version $lastUserVersion, array $currentVersion): bool
    {
        $lastVersion = $lastUserVersion->getVersion();

        // In case of addition or deletion in the version, there is a diff
        if (!empty(array_diff_key($lastVersion, $currentVersion))) {
            return true;
        }

        // If the spot or the slot has changed, there is a diff
        foreach ($currentVersion as $requestId => $request) {
            if ($lastVersion[$requestId]['slot'] !== $request['slot']
                || $lastVersion[$requestId]['spot'] !== $request['spot']
            ) {
                return true;
            }
        }

        return false;
    }
}
