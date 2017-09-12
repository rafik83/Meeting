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
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Domain\View\Spot\Import\SpotImportView;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
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

    /** @var TranslatorAdapter */
    private $translatorAdapter;

    /** @var string */
    private $importDir;

    /** @var SheetView[] */
    private $sheets = [];

    /** @var SpotImportView[] */
    private $spotImportViews = [];

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param ValidatorAdapter         $validatorAdapter
     * @param TranslatorAdapter        $translatorAdapter
     * @param string                   $importDir
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ValidatorAdapter $validatorAdapter,
        TranslatorAdapter $translatorAdapter,
        string $importDir
    ) {
        $this->importDir = $importDir;
        $this->sheetRepository = $sheetRepository;
        $this->validatorAdapter = $validatorAdapter;
        $this->translatorAdapter = $translatorAdapter;
    }

    /**
     * @param Event  $event
     * @param File   $spotImportedFile
     * @param string $locale
     *
     * @throws InvalidImportHeaderFileFormatException
     */
    public function import(Event $event, File $spotImportedFile, string $locale)
    {
        $content = file_get_contents($this->importDir . $spotImportedFile->getPath());

        // Mock of file content
        $columns = [
            ["reference", "size", "meetingCapacity","seatCapacity","active", "priority","visio", "sheets"],
            ['A1',"10","2","33",'zezrez', "4", "opa", "16938, 16931, 16919"],
            ["A2","10","2","33","1", "4", "1", "16566, 12"],
            ["A3","10","2","33","1", "4", "1", "16565"],
            ["A1","10","2","33","1", "4", "1", "16562"],
        ];

        if (!$this->areGivenKeysAllowed($columns[0])) {
            throw new InvalidImportHeaderFileFormatException('Headers line of csv are invalid');
        }

        unset($columns[0]);

        foreach ($columns as $row) {
            $sheetIds = explode(',', $row[7]);
            $errorMessage = [];


            if ($this->isSpotAlreadyAffected($row[0])) {
                $errorMessage['reference'] = $this->translatorAdapter->trans('validators.spot.reference.affected', [], 'validators', $locale);
            }

            $spot = new Import(
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4],
                $row[5],
                $row[6]
            );

            $validations = $this->validatorAdapter->validate($spot, ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE);

            $sheetViews = $this->handleSheetViewsByIds($event, $sheetIds, $locale);

            $this->spotImportViews[] = new SpotImportView($spot, $sheetViews, $errorMessage);
        }
        dump($this->spotImportViews);
    }

    /**
     * Return true if given keys are exactly the same than in array self::ALLOWED_KEYS
     * Return false otherwise
     *
     * @param array $keys
     *
     * @return bool
     */
    private function areGivenKeysAllowed(array $keys): bool
    {
        return empty(array_diff($keys, self::ALLOWED_KEYS));
    }

    /**
     * @param Event  $event
     * @param array  $sheetIds
     * @param string $locale
     *
     * @return SheetView[] indexed by Sheet id
     */
    private function handleSheetViewsByIds(Event $event, array $sheetIds, string $locale):? array
    {
        $sheets = [];

        foreach ($sheetIds as $sheetId) {
            $sheetView = $this->sheetRepository->getSheetViewsByEventById($event, $sheetId);

            if ($sheetView !== null) {
                if (isset($this->sheets[$sheetView->id])) {
                    $sheets[$sheetView->id] = $this->translatorAdapter->trans(
                        'validators.spot.sheet.already_imported',
                        [],
                        'validators',
                        $locale
                    );
                } else {
                    $sheets[$sheetView->id] = $sheetView;
                    $this->sheets[$sheetView->id] = $sheetView;
                }
            } else {
                $sheets[$sheetId] = $this->translatorAdapter->trans(
                    'validators.spot.sheet.not_exist',
                    [],
                    'validators',
                    $locale
                );
            }
        }

        return $sheets;
    }

    /**
     * @param string $reference
     *
     * @return bool
     */
    private function isSpotAlreadyAffected(string $reference): bool
    {
        foreach ($this->spotImportViews as $importedSpot) {
            return $importedSpot->import->reference === $reference;
        }

        return false;
    }
}
