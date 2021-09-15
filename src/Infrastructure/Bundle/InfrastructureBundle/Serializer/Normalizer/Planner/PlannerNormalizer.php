<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\PlannerView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlannerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            'dayList'          => [],
            'slotList'         => [],
            'typeList'         => [],
            'typePriorityList' => [],
            'sheetList'        => [],
            'userList'         => [],
            'spotList'         => [],
            'meetingList'      => [],
        ];

        if (!empty($object->dayList)) {
            $data['dayList'] = [
                'Day' => $this->normalizer->normalize($object->dayList, $format, $context),
            ];
        }

        if (!empty($object->slotList)) {
            $data['slotList'] = [
                'Slot' => $this->normalizer->normalize($object->slotList, $format, $context),
            ];
        }

        if (!empty($object->typeList)) {
            $data['typeList'] = [
                'Type' => $this->normalizer->normalize($object->typeList, $format, $context),
            ];
        }

        if (!empty($object->typePriorityList)) {
            $data['typePriorityList'] = [
                'TypePriority' => $this->normalizer->normalize($object->typePriorityList, $format, $context),
            ];
        }

        if (!empty($object->sheetList)) {
            $data['sheetList'] = [
                'Sheet' => $this->normalizer->normalize($object->sheetList, $format, $context),
            ];
        }

        if (!empty($object->userList)) {
            /** @var ParticipantView[] $users */
            $userList = [];
            $users = $object->userList;

            // Users deduplication
            foreach ($users as $participantView) {
                $userList[$participantView->userId] = $participantView;
            }

            $data['userList'] = [
                'User' => $this->normalizer->normalize($userList, $format, $context),
            ];
        }

        if (!empty($object->spotList)) {
            $data['spotList'] = [
                'Spot' => $this->normalizer->normalize($object->spotList, $format, $context),
            ];
        }

        if (!empty($object->meetingList)) {
            $data['meetingList'] = [
                'Meeting' => $this->normalizer->normalize($object->meetingList, $format, $context),
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PlannerView;
    }

    /**
     * {@inheritdoc}
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
}
