<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution\SpotFillingRateSlotViewNormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SpotFillingRateSlotViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $begin = new \DateTime('2017-06-10 10:00:00.000');
        $end = new \DateTime('2017-06-10 11:30:00.000');
        $data = new SpotFillingRateSlotView($begin, $end, 85);

        $serializer = new Serializer(
            [new SpotFillingRateSlotViewNormalizer(), new DateTimeNormalizer(), new ObjectNormalizer()],
            [new JsonEncoder()]
        );
        $result = $serializer->serialize($data, 'json');

        $expected = '{"begin":"2017-06-10T10:00:00+00:00","end":"2017-06-10T11:30:00+00:00","fillingRate":85}';

        $this->assertEquals($expected, $result);
    }
}
