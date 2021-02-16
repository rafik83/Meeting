<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Exception\NotBooleanValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MeetingNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param MeetingView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        // The boolean value are return as 1 or 0
        // therefore, the boolean must be change to string true or false to be interpreted
        $data = [
            '@id'             => $object->reference,
            'id'              => $object->id,
            'isVisio'         => $this->getBooleanValue($object->isVisio),
            'blockedSpot'     => $this->getBooleanValue($object->isBlockedSpot),
            'blockedSlot'     => $this->getBooleanValue($object->isBlockedSlot),
            'sheetList'       => [
                'Sheet' => array_map(function (SheetView $sheet) {
                    return ['@reference' => $sheet->reference];
                }, $object->sheetList),
            ],
            'userList' => [
                'User' => array_map(function (ParticipantView $participantView) {
                    return ['@reference' => $participantView->reference];
                }, $object->participantList),
            ],
        ];

        if ($object->hasSpot()) {
            $data['spot'] = ['@reference' => $object->getSpotReference()];
        }

        if ($object->hasSlot()) {
            $data['slot'] = ['@reference' => $object->getSlotReference()];
        }

        if ($object->hasLockedSpot()) {
            $data['lockedSpot'] = ['@reference' => $object->getLockedSpotReference()];
        }

        if ($object->hasLockedSlot()) {
            $data['lockedSlot'] = ['@reference' => $object->getLockedSlotReference()];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MeetingView;
    }

    /**
     * @param mixed $value
     *
     * @throws NotBooleanValueException
     *
     * @return string
     */
    private function getBooleanValue($value)
    {
        if (!is_bool($value)) {
            throw new NotBooleanValueException();
        }

        return true === $value ? 'true' : 'false';
    }
}
