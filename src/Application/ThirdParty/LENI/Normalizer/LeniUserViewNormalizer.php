<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LeniUserViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var LeniUserView $userView */
        $userView = $object;

        $data = [
            LeniConstants::LENI_COL_CAB_2 => (string) $userView->id,
            LeniConstants::LENI_COL_EXTERNAL_KEY => $userView->id,
            LeniConstants::LENI_COL_COMPANY_NAME => mb_substr($userView->sheetName, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_CATEGORY => (string) $userView->categoryId,
            LeniConstants::LENI_COL_TYPE => (string) $userView->typeId,
            LeniConstants::LENI_COL_TITLE => LeniConstants::GENDER_MAPPING[$userView->gender] ?? '',
            LeniConstants::LENI_COL_FIRST_NAME => mb_substr($userView->firstName, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_LAST_NAME => mb_substr($userView->lastName, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_POSITION => mb_substr($userView->position, 0, LeniConstants::SHORT_FIELD),
            LeniConstants::LENI_COL_EMAIL => mb_substr($userView->email, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_MOBILE_PHONE => mb_substr($userView->mobile, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_PHONE_NUMBER => mb_substr($userView->phone, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_UNALLOCATED => $userView->planning->unallocated,
            LeniConstants::LENI_COL_COUNTRY => $userView->country,
            LeniConstants::LENI_COL_ATTENDANCE => LeniConstants::ATTENDANCE,
            LeniConstants::LENI_COL_LOCALE => $userView->locale,
        ];

        $dayNumber = 1;

        foreach ($userView->planning->days as $day) {
            $data[LeniConstants::LENI_COL_DAY . $dayNumber] = $day->planning;

            $dayNumber++;
        }

        // Set the previous LENI user id
        // Warning: always on end of the returned array
        if ($context['previousUserExtraData'] instanceof ExtraData) {
            $previousData = unserialize($context['previousUserExtraData']->getValue(), ['allowed_classes' => false]);

            if (isset($previousData[LeniConstants::LENI_COL_USER_ID])) {
                $data[LeniConstants::LENI_COL_USER_ID] = $previousData[LeniConstants::LENI_COL_USER_ID];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LeniUserView;
    }
}
