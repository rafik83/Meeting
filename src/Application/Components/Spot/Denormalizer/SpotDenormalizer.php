<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Spot\Denormalizer;

use Proximum\Vimeet\Application\Exception\Spot\Import\InvalidImportHeaderFileFormatException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Spot\Import;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SpotDenormalizer implements DenormalizerInterface
{
    const KEY_REFERENCE        = 'reference';
    const KEY_SIZE             = 'size';
    const KEY_MEETING_CAPACITY = 'meetingCapacity';
    const KEY_SEAT_CAPACITY    = 'seatCapacity';
    const KEY_ACTIVE           = 'active';
    const KEY_PRIORITY         = 'priority';
    const KEY_VISIO            = 'visio';
    const KEY_SHEETS           = 'sheets';

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

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!$context['event'] instanceof Event) {
            throw new \InvalidArgumentException();
        }

        $spots = [];
        foreach ($data as $row) {
            $row = $this->cleanRow($row);

            if (!$this->areGivenKeysAllowed(array_keys($row))) {
                throw new InvalidImportHeaderFileFormatException('Headers line of csv are invalid');
            }

            try {
                $import = new Import(
                    new Spot(
                        $row[self::KEY_REFERENCE],
                        $context['event'],
                        (float) $row[self::KEY_SIZE],
                        (int) $row[self::KEY_MEETING_CAPACITY],
                        (int) $row[self::KEY_SEAT_CAPACITY],
                        (bool) $row[self::KEY_ACTIVE],
                        (int) $row[self::KEY_PRIORITY],
                        (bool) $row[self::KEY_VISIO]
                    ),
                    $this->getSheetIdsToArrayFromString($row[self::KEY_SHEETS])
                );
            } catch(\Exception $exception) {
                $import = new Import(null);
                $import->addError($exception->getMessage());
            }

            $spots[] = $import;
        }

        return $spots;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $format === 'csv' && $type === Import::class;
    }

    /**
     * @param array $row
     *
     * @return array
     */
    private function cleanRow(array $row): array
    {
        return array_filter($row, function ($index) {
            if (!empty($index)) {
                return true;
            }
        }, ARRAY_FILTER_USE_KEY);
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
     * @param string $sheetIds
     *
     * @return array of sheet ids
     */
    private function getSheetIdsToArrayFromString(string $sheetIds): array
    {
        if (!empty($sheetIds)) {
            return explode(',', str_replace(' ', '', $sheetIds));
        }

        return [];
    }
}
