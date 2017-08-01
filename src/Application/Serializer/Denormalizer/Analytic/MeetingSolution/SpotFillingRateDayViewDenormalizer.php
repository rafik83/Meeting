<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SpotFillingRateDayViewDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === SpotFillingRateDayView::class
            && $format === 'json'
            && isset($data['day'])
            && isset($data['timeZone'])
            && isset($data['slotsFillingRate'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $spotFillingRateDay = new SpotFillingRateDayView(
            $this->denormalizer->denormalize($data['day'], \DateTime::class, $format),
            $data['timeZone']
        );

        foreach ($data['slotsFillingRate'] as $slot) {
            /** @var SpotFillingRateSlotView $slotFillingRate */
            $slotFillingRate = $this->denormalizer->denormalize($slot, SpotFillingRateSlotView::class, $format);

            $spotFillingRateDay->addSlotFillingRate($slotFillingRate);
        }

        return $spotFillingRateDay;
    }
}
