<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Spot;

use Proximum\Vimeet\Application\Exception\Spot\Import\InvalidImportHeaderFileFormatException;
use Proximum\Vimeet\Application\View\Spot\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

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

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var string */
    private $importDir;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param string $importDir
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        string $importDir
    ) {
        $this->importDir = $importDir;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param File $spotImportedFile
     *
     * @throws \Exception
     */
    public function import(Event $event, File $spotImportedFile)
    {
        $content = file_get_contents($this->importDir . $spotImportedFile->getPath());

        // Mock of file content
        $columns = [
            ["reference", "size", "meetingCapacity","seatCapacity","active", "priority","visio", "sheets"],
            "A1","10","2","33","1", "4", "1", "16938, 16931, 16919",
            "A2","10","2","33","1", "4", "1", "16566",
            "A3","10","2","33","1", "4", "1", "16565",
            "A1","10","2","33","1", "4", "1", "16562",
        ];

        if (!$this->isGivenKeysAreAllowed($columns[0])) {
            throw new InvalidImportHeaderFileFormatException('Headers line of csv are invalid');
        }

        dump($this->getSheetsViewsByEventByIds($event, [16938, 16931, 16919]));die;

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

    /**
     * @param Event $event
     * @param array $sheetIds
     *
     * @return SheetView[] indexed by Sheet id
     */
    private function getSheetsViewsByEventByIds(Event $event, array $sheetIds):? array
    {
        $sheets = [];

        foreach ($sheetIds as $sheetId) {
            $sheets[$sheetId] = $this->sheetRepository->getSheetViewsById($event, $sheetId);
        }

        return $sheets;
    }
}
