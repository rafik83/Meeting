<?php

namespace Proximum\Vimeet\Application\Components\Spot\Denormalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
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

    /** @var TranslatorInterface */
    private $translatorAdapter;

    /**
     * @param TranslatorInterface $translatorAdapter
     */
    public function __construct(TranslatorInterface $translatorAdapter)
    {
        $this->translatorAdapter = $translatorAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!$context['event'] instanceof Event) {
            throw new \InvalidArgumentException();
        }

        $spots = [];

        foreach ($data as $row) {
            $row = $this->cleanRow($row);

            try {
                if (!$this->areGivenKeysAllowed(self::ALLOWED_KEYS, $row)) {
                    throw new InvalidImportHeaderFileFormatException(
                        $this->translatorAdapter->trans('validators.spot.csv.invalid', [], 'validators')
                    );
                }

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
            } catch (\Exception $exception) {
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
        return 'csv' === $format && Import::class === $type;
    }

    /**
     * @param array $row
     *
     * @return array
     */
    private function cleanRow(array $row): array
    {
        return array_filter($row, function ($index) {
            return !empty($index);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Return true if given keys are exactly the same than in array self::ALLOWED_KEYS
     * Return false otherwise
     *
     * @param array $keys
     * @param array $row
     *
     * @return bool
     */
    private function areGivenKeysAllowed(array $keys, array &$row): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $sheetIds
     *
     * @return array of sheet ids
     */
    private function getSheetIdsToArrayFromString(string $sheetIds): array
    {
        if (!empty($sheetIds)) {
            return array_filter(explode(',', $sheetIds), 'strlen');
        }

        return [];
    }
}
