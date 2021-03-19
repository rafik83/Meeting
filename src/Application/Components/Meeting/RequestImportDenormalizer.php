<?php

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Application\Exception\Import\InvalidKeysException;
use Proximum\Vimeet\Application\View\MeetingRequest\Import\MeetingRequestRow;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RequestImportDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const KEY_EMAIL_FROM = 'Email_asker';
    private const KEY_EMAIL_TO = 'Email_asked';

    public const ALLOWED_KEYS = [
        self::KEY_EMAIL_FROM,
        self::KEY_EMAIL_TO,
    ];

    /**
     * @return MeetingRequestRow[]
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $meetingRequestRows = [];

        // If there is only one line on imported file, convert it to array
        if (isset($data[self::KEY_EMAIL_FROM])) {
            $data = [$data];
        }

        foreach ($data as $row) {
            if (false === $this->areKeysValid($row)) {
                throw new InvalidKeysException('validators.authentication_token.csv.invalid_keys');
            }

            $meetingRequestRow = new MeetingRequestRow();
            $meetingRequestRow->emailFrom = $row[self::KEY_EMAIL_FROM];
            $meetingRequestRow->emailTo = $row[self::KEY_EMAIL_TO];

            $meetingRequestRows[] = $meetingRequestRow;
        }

        return $meetingRequestRows;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'csv' === $format && MeetingRequestRow::class.'[]' === $type;
    }

    private function areKeysValid(array &$row): bool
    {
        return empty(array_diff(array_keys($row), self::ALLOWED_KEYS));
    }
}
