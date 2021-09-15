<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Participant\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Normalizer\Participant\Export\ParticipantListViewNormalizer;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Translator;

class ParticipantListViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $participantView1 = new ParticipantView(
            123,
            'typeTitle1',
            'sheetTitle1',
            true,
            1234,
            12345,
            'email1@example.net',
            '10/10/2017',
            true,
            true,
            123,
            [
                'day_123' => '10/10/2018 10:00',
            ],
            [
                'option_124' => 124,
                'option_125' => 125,
            ],
            [
                'AZERTY1' => 'content1',
                'AZERTY2' => 'content2',
                'AZERTY3' => 'content4',
            ],
            [],
            4,
            3,
            2,
            1,
            0,
            'es'
        );

        $participantView = new ParticipantView(
            124,
            'typeTitle1',
            'sheetTitle1',
            true,
            1244,
            12445,
            'email1@example.net',
            '10/10/2017',
            true,
            true,
            null,
            [
                'day_123' => null,
            ],
            [
                'option_124' => 124,
                'option_127' => 127,
            ],
            [
                'AZERTY1' => 'content1',
                'AZERTY8' => 'content8',
            ],
            [],
            5,
            6,
            7,
            8,
            9,
            'pt'
        );

        $participantListView = new ParticipantListView(
            'fr',
            [
                $participantView,
                $participantView1,
            ],
            [
                'day_123' => '10/10/2018',
            ],
            [
                'AZERTY1' => 'Field 1',
                'AZERTY2' => 'Field 2',
                'AZERTY3' => 'Field 3',
                'AZERTY4' => 'Field 4',
                'AZERTY5' => 'Field 5',
                'AZERTY6' => 'Field 6',
                'AZERTY7' => 'Field 7',
                'AZERTY8' => 'Field 8',
            ],
            [
                'participant_122' => 'participant product 1',
                'participant_123' => 'participant product 2',
                'option_124' => 'option product 1',
                'option_125' => 'option product 2',
                'option_126' => 'option product 3',
                'option_127' => 'option product 4',
                'option_128' => 'option product 5',
            ],
            []
        );

        $translator = new Translator('fr');
        $translatorAdapter = new TranslatorAdapter($translator);
        $normalizer = new ParticipantListViewNormalizer(
            $translatorAdapter
        );

        $serializer = new Serializer(
            [
                $normalizer,
                new ObjectNormalizer()
            ],
            [
                new CsvEncoder()
            ]
        );
        $result = $serializer->serialize($participantListView, 'csv');

        $expected = "sheet_id,participant_type,sheet_name,sheet_enable,user_id,participant_id,participant_email,"
                   . "participant_created_at,happening_subscriber,participation_paid,viewed_sheets,"
                   . "clicked_elements,requested_meetings,scheduled_meetings,chat_sessions_call_visio,participant_locale,"
                   . "AZERTY1,AZERTY2,AZERTY3,AZERTY4,AZERTY5,AZERTY6,AZERTY7,AZERTY8,day_123,participant_122,"
                   . "participant_123,option_124,option_125,option_126,option_127,option_128
admin.participant.export.fields.sheet_id,admin.participant.export.fields.participant_type,"
                   . "admin.participant.export.fields.sheet_name,admin.participant.export.fields.sheet_enable,"
                   . "admin.participant.export.fields.user_id,admin.participant.export.fields.participant_id,"
                   . "admin.participant.export.fields.participant_email,admin.participant.export.fields.participant_created_at,"
                   . "admin.participant.export.fields.happening_subscriber,admin.participant.export.fields.participation_paid,"
                   . "admin.participant.export.fields.viewed_sheets,admin.participant.export.fields.clicked_elements,"
                   . "admin.participant.export.fields.requested_meetings,admin.participant.export.fields.scheduled_meetings,"
                   . "admin.participant.export.fields.chat_sessions_call_visio,admin.participant.export.fields.participant_locale,"
                   . "\"Field 1\",\"Field 2\",\"Field 3\",\"Field 4\",\"Field 5\",\"Field 6\",\"Field 7\",\"Field 8\",admin.participant.export.fields.day_checkin,\"participant product 1\",\"participant product 2\",\"option product 1\",\"option product 2\",\"option product 3\",\"option product 4\",\"option product 5\"
124,typeTitle1,sheetTitle1,admin.participant.export.yes,1244,12445,email1@example.net,10/10/2017,admin.participant.export.yes,admin.participant.export.fields.participation_paid.paid,5,6,7,8,9,pt,content1,,,,,,,content8,,admin.participant.export.no,admin.participant.export.no,admin.participant.export.yes,admin.participant.export.no,admin.participant.export.no,admin.participant.export.yes,admin.participant.export.no
123,typeTitle1,sheetTitle1,admin.participant.export.yes,1234,12345,email1@example.net,10/10/2017,admin.participant.export.yes,admin.participant.export.fields.participation_paid.paid,4,3,2,1,0,es,content1,content2,content4,,,,,,\"10/10/2018 10:00\",admin.participant.export.no,admin.participant.export.yes,admin.participant.export.yes,admin.participant.export.yes,admin.participant.export.no,admin.participant.export.no,admin.participant.export.no
";

        $this->assertEquals($expected, $result);
    }
}
