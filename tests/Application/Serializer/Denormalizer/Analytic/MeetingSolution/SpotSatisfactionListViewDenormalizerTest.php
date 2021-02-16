<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SpotSatisfactionListViewDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SpotSatisfactionViewDenormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class SpotSatisfactionListViewDenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $data = json_decode('[{"spotId":301,"reference":"MTable A01","shared":true,"visio":false,"satisfaction":92},{"spotId":302,"reference":"MTable A02","shared":true,"visio":false,"satisfaction":100},{"spotId":303,"reference":"MTable A03","shared":true,"visio":false,"satisfaction":92},{"spotId":310,"reference":"MTable B01","shared":false,"visio":false,"satisfaction":57},{"spotId":311,"reference":"MTable B02","shared":true,"visio":false,"satisfaction":71},{"spotId":312,"reference":"MTable B03","shared":true,"visio":true,"satisfaction":71},{"spotId":313,"reference":"MTable B313","shared":true,"visio":false,"satisfaction":99,"priority":8}]', true);

        $serializer = new Serializer(
            [
                new SpotSatisfactionListViewDenormalizer(),
                new SpotSatisfactionViewDenormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );

        $result = $serializer->denormalize($data, SpotSatisfactionListView::class, 'json');

        $expected = new SpotSatisfactionListView();
        $expected->addSpotSatisfaction(new SpotSatisfactionView(301, 'MTable A01', true, false, null, 92));
        $expected->addSpotSatisfaction(new SpotSatisfactionView(302, 'MTable A02', true, false, null, 100));
        $expected->addSpotSatisfaction(new SpotSatisfactionView(303, 'MTable A03', true, false, null, 92));
        $expected->addSpotSatisfaction(new SpotSatisfactionView(310, 'MTable B01', false, false, null, 57));
        $expected->addSpotSatisfaction(new SpotSatisfactionView(311, 'MTable B02', true, false, null, 71));
        $expected->addSpotSatisfaction(new SpotSatisfactionView(312, 'MTable B03', true, true, null, 71));
        $expected->addSpotSatisfaction(new SpotSatisfactionView(313, 'MTable B313', true, false, 8, 99));

        $this->assertEquals($expected, $result);
    }
}
