<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\MeetingRequest;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestListView;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestView;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\MeetingRequest\MeetingRequestListViewNormalizer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Translator;

class MeetingRequestListViewNormalizerTest extends TestCase
{
    public function testNormalization()
    {
        $date = new \DateTime('2017-10-10 10:00:00');
        $slotBeginDate = new \DateTime('2017-10-10 10:20:00');
        $fromSheet1 = new SheetView(15, 'sheet title 1', 'type title 1', 'category title 1', [8, 9], ['toto', 'tata']);
        $fromSheet2 = new SheetView(16, 'sheet title 2', 'type title 2', null, [], []);
        $toSheet1 = new SheetView(17, 'sheet title 3', 'type title 3', null, [11], ['jean']);
        $toSheet2 = new SheetView(18, 'sheet title 4', 'type title 1', 'category title 1', [91], ['Paul']);
        $meetingRequestView1 = new MeetingRequestView(58, 8, $fromSheet1, $toSheet1, 'planned', $date, $date, Meeting::CREATED_BY_PLANNER, $slotBeginDate);
        $meetingRequestView2 = new MeetingRequestView(80, null, $fromSheet2, $toSheet2, 'refused', $date, $date, Meeting::CREATED_BY_PARTICIPANT, $slotBeginDate);
        $meetingRequests = [
            $meetingRequestView1,
            $meetingRequestView2,
        ];
        $meetingRequestListView = new MeetingRequestListView(
            $meetingRequests,
            'Europe/Paris',
            'fr'
        );

        $translator = $this->prophesize(TranslatorInterface::class);

        $translator
            ->trans('export.meeting_request.col.requestId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.requestId')
        ;

        $translator
            ->trans('export.meeting_request.col.meetingId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.meetingId')
        ;
        $translator
            ->trans('export.meeting_request.col.fromSheetId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.fromSheetId')
        ;
        $translator
            ->trans('export.meeting_request.col.fromSheetTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.fromSheetTitle')
        ;
        $translator
            ->trans('export.meeting_request.col.fromSheetType', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.fromSheetType')
        ;
        $translator
            ->trans('export.meeting_request.col.fromSheetCategory', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.fromSheetCategory')
        ;
        $translator
            ->trans('export.meeting_request.col.fromParticipantIds', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.fromParticipantIds')
        ;
        $translator
            ->trans('export.meeting_request.col.fromParticipantNames', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.fromParticipantNames')
        ;
        $translator
            ->trans('export.meeting_request.col.toSheetId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.toSheetId')
        ;
        $translator
            ->trans('export.meeting_request.col.toSheetTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.toSheetTitle')
        ;
        $translator
            ->trans('export.meeting_request.col.toSheetType', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.toSheetType')
        ;
        $translator
            ->trans('export.meeting_request.col.toSheetCategory', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.toSheetCategory')
        ;
        $translator
            ->trans('export.meeting_request.col.toParticipantIds', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.toParticipantIds')
        ;
        $translator
            ->trans('export.meeting_request.col.toParticipantNames', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.toParticipantNames')
        ;
        $translator
            ->trans('export.meeting_request.col.state', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.state')
        ;
        $translator
            ->trans('export.meeting_request.col.createdAt', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.createdAt')
        ;
        $translator
            ->trans('export.meeting_request.col.updatedAt', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.updatedAt')
        ;
        $translator
            ->trans('export.meeting_request.state.planned', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.state.planned')
        ;
        $translator
            ->trans('export.meeting_request.state.refused', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.state.refused')
        ;

        $translator
            ->trans('export.meeting_request.col.createdType.planner', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.createdType.planner');

        $translator
            ->trans('export.meeting_request.col.slot', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.createdType.slot');

        $translator
            ->trans('export.meeting_request.col.createdType', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.createdType');

        $translator
            ->trans('export.meeting_request.col.createdType.participant', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('trans.export.meeting_request.col.createdType.participant');

        $normalizer = new MeetingRequestListViewNormalizer($translator->reveal());
        $result = $normalizer->normalize($meetingRequestListView, 'csv', ['csv_delimiter' => ';']);

        $expected = [
            [
                'trans.export.meeting_request.col.requestId' => 58,
                'trans.export.meeting_request.col.meetingId' => 8,
                'trans.export.meeting_request.col.fromSheetId' => 15,
                'trans.export.meeting_request.col.fromSheetTitle' => 'sheet title 1',
                'trans.export.meeting_request.col.fromSheetType' => 'type title 1',
                'trans.export.meeting_request.col.fromSheetCategory' => 'category title 1',
                'trans.export.meeting_request.col.fromParticipantIds' => '8,9',
                'trans.export.meeting_request.col.fromParticipantNames' => 'toto,tata',
                'trans.export.meeting_request.col.toSheetId' => 17,
                'trans.export.meeting_request.col.toSheetTitle' => 'sheet title 3',
                'trans.export.meeting_request.col.toSheetType' => 'type title 3',
                'trans.export.meeting_request.col.toSheetCategory' => '',
                'trans.export.meeting_request.col.toParticipantIds' => '11',
                'trans.export.meeting_request.col.toParticipantNames' => 'jean',
                'trans.export.meeting_request.col.state' => 'trans.export.meeting_request.state.planned',
                'trans.export.meeting_request.col.createdAt' => '10/10/2017 12:00',
                'trans.export.meeting_request.col.updatedAt' => '10/10/2017 12:00',
                'trans.export.meeting_request.col.createdType' => 'trans.export.meeting_request.col.createdType.planner',
                'trans.export.meeting_request.col.createdType.slot' => '10/10/2017 12:20',
            ],
            [
                'trans.export.meeting_request.col.requestId' => 80,
                'trans.export.meeting_request.col.meetingId' => null,
                'trans.export.meeting_request.col.fromSheetId' => 16,
                'trans.export.meeting_request.col.fromSheetTitle' => 'sheet title 2',
                'trans.export.meeting_request.col.fromSheetType' => 'type title 2',
                'trans.export.meeting_request.col.fromSheetCategory' => '',
                'trans.export.meeting_request.col.fromParticipantIds' => '',
                'trans.export.meeting_request.col.fromParticipantNames' => '',
                'trans.export.meeting_request.col.toSheetId' => 18,
                'trans.export.meeting_request.col.toSheetTitle' => 'sheet title 4',
                'trans.export.meeting_request.col.toSheetType' => 'type title 1',
                'trans.export.meeting_request.col.toSheetCategory' => 'category title 1',
                'trans.export.meeting_request.col.toParticipantIds' => '91',
                'trans.export.meeting_request.col.toParticipantNames' => 'Paul',
                'trans.export.meeting_request.col.state' => 'trans.export.meeting_request.state.refused',
                'trans.export.meeting_request.col.createdAt' => '10/10/2017 12:00',
                'trans.export.meeting_request.col.updatedAt' => '10/10/2017 12:00',
                'trans.export.meeting_request.col.createdType' => 'trans.export.meeting_request.col.createdType.participant',
                'trans.export.meeting_request.col.createdType.slot' => '10/10/2017 12:20'
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testNormalize()
    {
        $date = new \DateTime('2017-10-10 10:00:00');
        $slotBeginDate = new \DateTime('2017-10-10 10:20:00');
        $fromSheet1 = new SheetView(15, 'sheet title 1', 'type title 1', 'category title 1', [8, 9], ['toto', 'tata']);
        $fromSheet2 = new SheetView(16, 'sheet title 2', 'type title 2', null, [], []);
        $toSheet1 = new SheetView(17, 'sheet title 3', 'type title 3', null, [11], ['jean']);
        $toSheet2 = new SheetView(18, 'sheet title 4', 'type title 1', 'category title 1', [91], ['Paul']);
        $meetingRequestView1 = new MeetingRequestView(58, 8, $fromSheet1, $toSheet1, 'planned', $date, $date, Meeting::CREATED_BY_PARTICIPANT, $slotBeginDate);
        $meetingRequestView2 = new MeetingRequestView(80, null, $fromSheet2, $toSheet2, 'refused', $date, $date, Meeting::CREATED_BY_ADMIN, $slotBeginDate);
        $meetingRequests = [
            $meetingRequestView1,
            $meetingRequestView2,
        ];
        $meetingRequestListView = new MeetingRequestListView(
            $meetingRequests,
            'Europe/Paris',
            'fr'
        );

        $translator = new Translator('fr');
        $translatorAdapter = new TranslatorAdapter($translator);
        $serializer = new Serializer(
            [
                new MeetingRequestListViewNormalizer($translatorAdapter),
                new ObjectNormalizer(),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $result = $serializer->serialize($meetingRequestListView, 'csv', ['csv_delimiter' => ';']);

        $expected = 'export.meeting_request.col.requestId;export.meeting_request.col.meetingId;export.meeting_request.col.fromSheetId;export.meeting_request.col.fromSheetTitle;export.meeting_request.col.fromSheetType;export.meeting_request.col.fromSheetCategory;export.meeting_request.col.fromParticipantIds;export.meeting_request.col.fromParticipantNames;export.meeting_request.col.toSheetId;export.meeting_request.col.toSheetTitle;export.meeting_request.col.toSheetType;export.meeting_request.col.toSheetCategory;export.meeting_request.col.toParticipantIds;export.meeting_request.col.toParticipantNames;export.meeting_request.col.state;export.meeting_request.col.createdAt;export.meeting_request.col.updatedAt;export.meeting_request.col.createdType;export.meeting_request.col.slot
58;8;15;"sheet title 1";"type title 1";"category title 1";8,9;toto,tata;17;"sheet title 3";"type title 3";;11;jean;export.meeting_request.state.planned;"10/10/2017 12:00";"10/10/2017 12:00";export.meeting_request.col.createdType.participant;"10/10/2017 12:20"
80;;16;"sheet title 2";"type title 2";;;;18;"sheet title 4";"type title 1";"category title 1";91;Paul;export.meeting_request.state.refused;"10/10/2017 12:00";"10/10/2017 12:00";export.meeting_request.col.createdType.admin;"10/10/2017 12:20"
';

        $this->assertEquals($expected, $result);
    }
}
