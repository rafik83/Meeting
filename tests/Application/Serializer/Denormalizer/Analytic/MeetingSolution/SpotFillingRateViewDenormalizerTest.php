<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SpotFillingRateDayViewDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SpotFillingRateSlotViewDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SpotFillingRateViewDenormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

class SpotFillingRateViewDenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $data = json_decode("{\"2017-02-01\":{\"day\":\"2017-02-01T09:00:00+00:00\",\"timeZone\":\"Europe\/Paris\",\"slotsFillingRate\":[{\"begin\":\"2017-02-01T09:00:00+00:00\",\"end\":\"2017-02-01T09:25:00+00:00\",\"fillingRate\":35},{\"begin\":\"2017-02-01T09:30:00+00:00\",\"end\":\"2017-02-01T09:55:00+00:00\",\"fillingRate\":45},{\"begin\":\"2017-02-01T10:00:00+00:00\",\"end\":\"2017-02-01T10:25:00+00:00\",\"fillingRate\":54}]}}", true);

        $serializer = new Serializer(
            [
                new SpotFillingRateViewDenormalizer(),
                new SpotFillingRateDayViewDenormalizer(),
                new SpotFillingRateSlotViewDenormalizer(),
                new DateTimeNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
        $result = $serializer->denormalize($data, SpotFillingRateDayListView::class, 'json');

        $expected = new SpotFillingRateDayListView();
        $spotFillingRateDayView = new SpotFillingRateDayView(
            new \DateTime('2017-02-01T09:00:00+00:00'),
            'Europe/Paris'
        );
        $slotFillingRateView1 = new SpotFillingRateSlotView(
            new \DateTime('2017-02-01T09:00:00+00:00'),
            new \DateTime('2017-02-01T09:25:00+00:0'),
            35
        );
        $slotFillingRateView2 = new SpotFillingRateSlotView(
            new \DateTime('2017-02-01T09:30:00+00:00'),
            new \DateTime('2017-02-01T09:55:00+00:00'),
            45
        );
        $slotFillingRateView3 = new SpotFillingRateSlotView(
            new \DateTime('2017-02-01T10:00:00+00:00'),
            new \DateTime('2017-02-01T10:25:00+00:00'),
            54
        );
        $spotFillingRateDayView->addSlotFillingRate($slotFillingRateView1);
        $spotFillingRateDayView->addSlotFillingRate($slotFillingRateView2);
        $spotFillingRateDayView->addSlotFillingRate($slotFillingRateView3);
        $expected->addSpotFillingRateDayView($spotFillingRateDayView);

        $this->assertEquals($expected, $result);
    }
}
