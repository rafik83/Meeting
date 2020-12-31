<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SheetTemplateManager
{
    use TemplateCreateBlockTrait;

    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    public function __construct(SheetTemplateRepositoryInterface $sheetTemplateRepository)
    {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
    }

    public function create(?Event $event, ?array $nomenclatures): SheetTemplate
    {
        $data = [
            '69b3cde3' => $this->block(),
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

        $sheetTemplate = new SheetTemplate(
            'SheetTemplate',
            $data,
            $event ? $event->getLocales() : ['fr'],
            $event ? $event->getLocaleFallback() : 'fr',
            new \DateTime(),
            ['dcc42d3d'],
            $event
        );

        $this->sheetTemplateRepository->add($sheetTemplate);

        return $sheetTemplate;
    }

    private function block()
    {
        $children = [
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
            '03b394ac' => [
                'component' => 'object',
                'type' => 'nomenclature',
                'config' => [
                    'mode' => 'checkboxes',
                    'label' => ['fr' => 'Offres'],
                    'placeholder' => ['fr' => 'Vos offres'],
                    'help' => ['fr' => 'Vos offres'],
                    'nomenclature' => $nomenclatures['services'] ?? 1,
                    'objective' => 'supply',
                ],
            ],
            '63ccc105' => [
                'component' => 'object',
                'type' => 'nomenclature',
                'config' => [
                    'mode' => 'checkboxes',
                    'label' => ['fr' => 'Besoins'],
                    'placeholder' => ['fr' => 'Vos besoins'],
                    'help' => ['fr' => 'Vos besoins'],
                    'nomenclature' => $nomenclatures['services'] ?? 1,
                    'objective' => 'need',
                ],
            ],
        ];

        return $this->createBlock($children);
    }
}
