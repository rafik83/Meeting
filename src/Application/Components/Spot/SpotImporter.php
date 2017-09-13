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
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Domain\View\Spot\Import\SpotImportView;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class SpotImporter
{
    const KEY_REFERENCE = 'reference';
    const KEY_SIZE = 'size';
    const KEY_MEETING_CAPACITY = 'meetingCapacity';
    const KEY_SEAT_CAPACITY = 'seatCapacity';
    const KEY_ACTIVE = 'active';
    const KEY_PRIORITY = 'priority';
    const KEY_VISIO = 'visio';
    const KEY_SHEETS = 'sheets';

    const ALLOWED_KEYS = [
        self::KEY_REFERENCE,
        self::KEY_SIZE,
        self::KEY_MEETING_CAPACITY,
        self::KEY_SEAT_CAPACITY,
        self::KEY_ACTIVE,
        self::KEY_PRIORITY,
        self::KEY_VISIO,
        self::KEY_SHEETS,
    ];

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ValidatorInterface */
    private $validatorAdapter;

    /** @var TranslatorAdapter */
    private $translatorAdapter;

    /*** @var SerializerAdapter */
    private $serializerAdapter;

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
     * @param SerializerAdapter        $serializerAdapter
     * @param string                   $importDir
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ValidatorAdapter $validatorAdapter,
        TranslatorAdapter $translatorAdapter,
        SerializerAdapter $serializerAdapter,
        string $importDir
    ) {
        $this->importDir = $importDir;
        $this->sheetRepository = $sheetRepository;
        $this->validatorAdapter = $validatorAdapter;
        $this->translatorAdapter = $translatorAdapter;
        $this->serializerAdapter = $serializerAdapter;
    }

    /**
     * @param Event  $event
     * @param File   $spotImportedFile
     * @param string $locale
     *
     * @return SpotImportView[]
     * @throws InvalidImportHeaderFileFormatException
     */
    public function import(Event $event, File $spotImportedFile, string $locale): array
    {
        $rows = $this->serializerAdapter->deserialize(
            file_get_contents($this->importDir . $spotImportedFile->getPath()),
            Spot::class,
            'csv'
        );

        $this->checkCsvHeaders(array_keys($rows[0]));

        foreach ($rows as $row) {
            $sheetIds = $this->getSheetIdsToArrayFromString($row[self::KEY_SHEETS]);
            $errorMessage = [];

            if ($this->isSpotAlreadyImported($row[self::KEY_REFERENCE])) {
                $errorMessage[self::KEY_REFERENCE] = $this
                    ->translatorAdapter
                    ->trans('validators.spot.reference.affected', [], 'validators', $locale);
            }

            $spot = new Import(
                $row[self::KEY_REFERENCE],
                $row[self::KEY_SIZE],
                $row[self::KEY_MEETING_CAPACITY],
                $row[self::KEY_SEAT_CAPACITY],
                $row[self::KEY_ACTIVE],
                $row[self::KEY_PRIORITY],
                $row[self::KEY_VISIO]
            );

            $validations = $this->validatorAdapter->validate($spot, ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE);

            foreach ($validations as $validation) {
                $errorMessage['validations'] = $validation;
            }

            $sheetViews = $this->handleSheetViewsByIds($event, $sheetIds, $locale);

            $this->spotImportViews[] = new SpotImportView($spot, $sheetViews, $errorMessage);
        }

        return $this->spotImportViews;
    }

    /**
     * @param Event  $event
     * @param array  $sheetIds
     * @param string $locale
     *
     * @return SheetView[] indexed by Sheet id
     */
    private function handleSheetViewsByIds(Event $event, array $sheetIds, string $locale): array
    {
        $sheets = [];

        foreach ($sheetIds as $sheetId) {
            $sheetView = $this->sheetRepository->getSheetViewsByEventById($event, $sheetId);

            if ($sheetView !== null) {
                if (isset($this->sheets[$sheetView->id])) {
                    $sheets[$sheetView->id] = new SheetView(
                        $sheetId,
                        $this->translatorAdapter->trans(
                            'validators.spot.sheet.already_imported',
                            [],
                            'validators',
                            $locale
                        )
                    );
                } else {
                    $sheets[$sheetView->id] = $sheetView;
                    $this->sheets[$sheetView->id] = $sheetView;
                }
            } else {
                $sheets[$sheetId] = new SheetView(
                    $sheetId,
                    $this->translatorAdapter->trans(
                        'validators.spot.sheet.not_exist',
                        [],
                        'validators',
                        $locale
                    )
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
    private function isSpotAlreadyImported(string $reference): bool
    {
        foreach ($this->spotImportViews as $importedSpot) {
            return $importedSpot->import->reference === $reference;
        }

        return false;
    }

    /**
     * @param string $sheetIds
     *
     * @return array of sheet ids
     */
    private function getSheetIdsToArrayFromString(string $sheetIds): array
    {
        return explode(',', str_replace(' ', '', $sheetIds));
    }

    /**
     * @param array $keys
     * @throws InvalidImportHeaderFileFormatException
     */
    private function checkCsvHeaders(array $keys)
    {
        if (!$this->areGivenKeysAllowed($keys)) {
            throw new InvalidImportHeaderFileFormatException('Headers line of csv are invalid');
        }
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
}
