<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution\SpotFillingRateDayViewNormalizer;
use Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution\SpotFillingRateSlotViewNormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SpotFillingRateDayViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $date   = new \DateTime('2017-06-10 10:00:00.000');
        $begin1 = new \DateTime('2017-06-10 10:00:00.000');
        $begin2 = new \DateTime('2017-06-10 11:00:00.000');
        $begin3 = new \DateTime('2017-06-10 12:00:00.000');
        $end1 = new \DateTime('2017-06-10 10:25:00.000');
        $end2 = new \DateTime('2017-06-10 11:25:00.000');
        $end3 = new \DateTime('2017-06-10 12:25:00.000');

        $data = new SpotFillingRateDayView($date, 'Europe/Paris');
        $data->addSlotFillingRate(new SpotFillingRateSlotView($begin1, $end1, 42));
        $data->addSlotFillingRate(new SpotFillingRateSlotView($begin2, $end2, 87));
        $data->addSlotFillingRate(new SpotFillingRateSlotView($begin3, $end3, 100));

        $serializer = new Serializer(
            [
                new SpotFillingRateDayViewNormalizer(),
                new SpotFillingRateSlotViewNormalizer(),
                new DateTimeNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
        $result = $serializer->serialize($data, 'json');

        $expected = "{\"day\":\"2017-06-10T10:00:00+00:00\",\"timeZone\":\"Europe\/Paris\",\"slotsFillingRate\":[{\"begin\":\"2017-06-10T10:00:00+00:00\",\"end\":\"2017-06-10T10:25:00+00:00\",\"fillingRate\":42},{\"begin\":\"2017-06-10T11:00:00+00:00\",\"end\":\"2017-06-10T11:25:00+00:00\",\"fillingRate\":87},{\"begin\":\"2017-06-10T12:00:00+00:00\",\"end\":\"2017-06-10T12:25:00+00:00\",\"fillingRate\":100}]}";

        $this->assertEquals($expected, $result);
    }
}
