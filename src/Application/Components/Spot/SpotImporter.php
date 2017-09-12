<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Spot;

use Proximum\Vimeet\Domain\Model\File;

class SpotImporter
{
    const ALLOWED_KEYS = [
        "reference",
        "size",
        "meetingCapacity",
        "seatCapacity",
        "active",
        "priority",
        "visio",
        "sheets",
    ];

    /** @var string */
    private $importDir;

    /**
     * @param string $importDir
     */
    public function __construct(string $importDir)
    {
        $this->importDir = $importDir;
    }

    /**
     * @param File $spotImportedFile
     */
    public function import(File $spotImportedFile)
    {
        $content = file_get_contents($this->importDir . $spotImportedFile->getPath());
        dump($content);
    }

    /**
     * Return true if given keys are exactly the same than in array self::ALLOWED_KEYS
     * Return false otherwise
     *
     * @param array $keys
     *
     * @return bool
     */
    private function isGivenKeysAreAllowed(array $keys): bool
    {
        return empty(array_diff($keys, self::ALLOWED_KEYS));
    }
}
