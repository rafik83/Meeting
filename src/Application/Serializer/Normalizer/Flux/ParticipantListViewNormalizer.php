<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Flux;

use Proximum\Vimeet\Application\View\Flux\ParticipantListView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParticipantListViewNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array()): array
    {
        /** @var ParticipantListView $participantListView */
        $participantListView = $object;
        $data = [];

        foreach ($participantListView->participantViews as $participantView) {
            $data[] = [
                'company' => $participantView->sheetView->title,
                'logo' => $participantView->sheetView->logo,
                'type' => $participantView->sheetView->type,
                'description' => $participantView->sheetView->description,
                'country' => $participantView->sheetView->country,
                'initials' => $participantView->initials,
                'position' => $participantView->position,
                'register' => $participantView->registrationDate,
            ];
        }

        return ['participant' => $data];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ParticipantListView;
    }
}
