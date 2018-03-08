<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution\SpotSatisfactionViewNormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SpotSatisfactionViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $data = new SpotSatisfactionView(12, 'Reference of the spot', true, true, 64);

        $serializer = new Serializer(
            [
                new SpotSatisfactionViewNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );

        $result = $serializer->serialize($data, 'json');

        $expected = '{"spotId":12,"reference":"Reference of the spot","shared":true,"visio":true,"satisfaction":64}';

        $this->assertEquals($expected, $result);
    }
}
