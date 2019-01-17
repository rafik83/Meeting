<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Flux;

use Proximum\Vimeet\Application\Serializer\Normalizer\Flux\ParticipantListViewNormalizer;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Flux\ParticipantListView;
use Proximum\Vimeet\Application\View\Flux\ParticipantView;
use Proximum\Vimeet\Application\View\Flux\SheetView;

class ParticipantListViewNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $registrationDate = new \DateTime("2019-01-01 12:00:00.000");
        $data = new ParticipantListView([
            new ParticipantView(
                'MM',
                'Développeur',
                $registrationDate,
                new SheetView('agence', 'ELAO', 'Agence web', 'FR', '')
            ),
            new ParticipantView(
                'ND',
                'Développeur',
                $registrationDate,
                new SheetView('agence', 'ELAO', 'Agence web', 'FR', '')
            ),
            new ParticipantView(
                'SS',
                'Coach agile',
                $registrationDate,
                new SheetView('agence', 'TA Consulting', '', 'FR', 'http://monsite.com/uploads/mon-logo.png')
            )
        ]);

        $participantListViewNormalizer = new ParticipantListViewNormalizer();
        $result = $participantListViewNormalizer->normalize($data);
        $expectedResult = [
            [
                'company' => 'ELAO',
                'logo' => '',
                'type' => 'agence',
                'description' => 'Agence web',
                'country' => 'FR',
                'initials' => 'MM',
                'position' => 'Développeur',
                'register' => $registrationDate,
            ],
            [
                'company' => 'ELAO',
                'logo' => '',
                'type' => 'agence',
                'description' => 'Agence web',
                'country' => 'FR',
                'initials' => 'ND',
                'position' => 'Développeur',
                'register' => $registrationDate,
            ],
            [
                'company' => 'TA Consulting',
                'logo' => 'http://monsite.com/uploads/mon-logo.png',
                'type' => 'agence',
                'description' => '',
                'country' => 'FR',
                'initials' => 'SS',
                'position' => 'Coach agile',
                'register' => $registrationDate,
            ],
        ];

        $this->assertEquals($result, $expectedResult);
    }
}
