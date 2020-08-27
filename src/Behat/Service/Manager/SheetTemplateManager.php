<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SheetTemplateManager
{
    private const DEFAULT_VALUE = [
        '69b3cde3' => [
            'component' => 'block',
            'type' => '8-4',
            'config' => ['style' => 'style-1'],
            'children' => [[
                'dcc42d3d' => [
                    'component' => 'object',
                    'type' => 'editable-text',
                    'config' => [
                        'style' => 'style-1',
                        'label' => ['fr' => 'Titre de votre fiche', 'en' => 'Title of your sheet'],
                        'placeholder' => ['fr' => 'Société en une phrase', 'en' => 'Your company in a sentence'],
                        'help' => ['fr' => '', 'en' => ''],
                        'length' => '200',
                        'type' => 'title',
                        'required' => 'true',
                    ],
                ],
            ]],
        ],
        'bef61d39' => [
            'component' => 'object',
            'type' => 'participant',
            'config' => [
                'style' => 'style-1',
                'label' => ['fr' => 'Participants', 'en' => 'Participants'],
                'numberOfParticipantShown' => '3',
            ],

        ],
    ];

    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    public function __construct(SheetTemplateRepositoryInterface $sheetTemplateRepository)
    {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
    }

    public function create(?Event $event): SheetTemplate
    {
        $sheetTemplate = new SheetTemplate(
            'SheetTemplate',
            self::DEFAULT_VALUE,
            $event ? $event->getLocales() : ['fr'],
            $event ? $event->getLocaleFallback() : 'fr',
            new \DateTime(),
            ['dcc42d3d'],
            $event
        );

        $this->sheetTemplateRepository->add($sheetTemplate);

        return $sheetTemplate;
    }
}
