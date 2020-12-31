<?php

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
        if (!empty(array_diff_key($lastVersion, $currentVersion))
            || !empty(array_diff_key($currentVersion, $lastVersion))
        ) {
            return true;
        }

        if ((empty($lastVersion) && !empty($currentVersion))
            || (!empty($lastVersion) && empty($currentVersion))
        ) {
            return true;
        }

        // If the spot or the slot has changed, there is a diff
        foreach ($currentVersion as $requestId => $request) {
            if ($this->checkTwoVersion($lastVersion, $requestId, $request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $versionToCheck
     * @param int   $requestId
     * @param array $request
     *
     * @return bool
     */
    public function checkTwoVersion(array &$versionToCheck, int $requestId, array $request): bool
    {
        if (!isset($versionToCheck[$requestId])) {
            throw new \InvalidArgumentException('the given version is not compatible');
        }

        return $versionToCheck[$requestId]['slot'] !== $request['slot']
            || $versionToCheck[$requestId]['spot'] !== $request['spot']
        ;
    }
}
