<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;

/**
 * This class is used to determine if there is a diff in the current version and the last save version
 */
class DiffChecker
{
    /** @var Generator */
    private $generator;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /**
     * @param Generator                  $generator
     * @param VersionRepositoryInterface $versionRepository
     */
    public function __construct(Generator $generator, VersionRepositoryInterface $versionRepository)
    {
        $this->generator = $generator;
        $this->versionRepository = $versionRepository;
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return bool
     */
    public function hasDiff(Event $event, User $user): bool
    {
        $userVersion = $this->versionRepository->getLastVersionByEventAndUser($event, $user);

        if (null === $userVersion) {
            return false;
        }

        $lastVersion = $userVersion->getVersion();
        $currentVersion = $this->generator->generate($event, $user);

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
