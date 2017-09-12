<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Spot;

use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Application\Exception\Spot\Import\InvalidImportHeaderFileFormatException;
use Proximum\Vimeet\Application\View\Spot\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;

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

    /** @var ValidatorInterface */
    private $validatorAdapter;

    /** @var string */
    private $importDir;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param ValidatorAdapter         $validatorAdapter
     * @param string                   $importDir
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ValidatorAdapter $validatorAdapter,
        string $importDir
    ) {
        $this->importDir = $importDir;
        $this->sheetRepository = $sheetRepository;
        $this->validatorAdapter = $validatorAdapter;
    }

    /**
     * @param Event $event
     * @param File  $spotImportedFile
     *
     * @throws InvalidImportHeaderFileFormatException
     */
    public function import(Event $event, File $spotImportedFile)
    {
        $content = file_get_contents($this->importDir . $spotImportedFile->getPath());

        // Mock of file content
        $columns = [
            ["reference", "size", "meetingCapacity","seatCapacity","active", "priority","visio", "sheets"],
            [false,"10","2","33","1", "4", "1", "16938, 16931, 16919"],
            ["A2","10","2","33","1", "4", "1", "16566"],
            ["A3","10","2","33","1", "4", "1", "16565"],
            ["A1","10","2","33","1", "4", "1", "16562"],
        ];

        if (!$this->areGivenKeysAreAllowed($columns[0])) {
            throw new InvalidImportHeaderFileFormatException('Headers line of csv are invalid');
        }

        unset($columns[0]);



        foreach ($columns as $row) {
            $sheetIds = explode(',', $row[7]);
            $sheetViews = $this->getSheetsViewsByEventByIds($event, $sheetIds);

            $spot = new Import(
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4],
                $row[5],
                $row[6],
                $sheetViews
            );

            $validations = $this->validatorAdapter->validate($spot, ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE);
            // make an error spool indexed by key
        };


    }

    /**
     * Return true if given keys are exactly the same than in array self::ALLOWED_KEYS
     * Return false otherwise
     *
     * @param array $keys
     *
     * @return bool
     */
    private function areGivenKeysAreAllowed(array $keys): bool
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
