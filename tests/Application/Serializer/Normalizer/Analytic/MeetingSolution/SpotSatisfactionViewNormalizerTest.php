<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution\SpotSatisfactionViewNormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SpotSatisfactionViewNormalizerTest extends TestCase
{
    private $serializer;

    public function setUp()
    {
        $this->serializer = new Serializer(
            [
                new SpotSatisfactionViewNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
    }

    public function test_normalize_without_priority()
    {
        $data = new SpotSatisfactionView(12, 'Reference of the spot', true, true, null, 64);
        $result = $this->serializer->serialize($data, 'json');

        $expected = '{"spotId":12,"reference":"Reference of the spot","shared":true,"visio":true,"satisfaction":64,"priority":null}';
        $this->assertEquals($expected, $result);
    }

    public function test_normalize_with_priority()
    {
        $data = new SpotSatisfactionView(12, 'Reference of the spot', true, true, 8, 64);
        $result = $this->serializer->serialize($data, 'json');

        $expected = '{"spotId":12,"reference":"Reference of the spot","shared":true,"visio":true,"satisfaction":64,"priority":8}';
        $this->assertEquals($expected, $result);
    }
}
