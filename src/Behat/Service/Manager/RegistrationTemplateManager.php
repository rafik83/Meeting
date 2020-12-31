<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class RegistrationTemplateManager
{
    use TemplateCreateBlockTrait;

    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    public function __construct(RegistrationTemplateRepositoryInterface $registrationTemplateRepository)
    {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
    }

    public function create2stepsTemplate(?Event $event, ?array $nomenclatures): RegistrationTemplate
    {
        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [
                '211b2168' => $this->step1Block($nomenclatures),
                '211b2169' => $this->step2Block(),
            ],
            $event ? $event->getLocales() : ['fr'],
            $event ? $event->getLocaleFallback() : 'fr',
            new \DateTime(),
            $event
        );

        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }

    public function create1stepTemplate(?Event $event, ?array $nomenclatures): RegistrationTemplate
    {
        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [
                '211b2168' => $this->step1Block($nomenclatures),
            ],
            $event ? $event->getLocales() : ['fr'],
            $event ? $event->getLocaleFallback() : 'fr',
            new \DateTime(),
            $event
        );

        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }

    private function step1Block($nomenclatures)
    {
        $children = [
            'dded0597' => [
                'component' => 'object',
                'type' => 'text',
                'config' => [
                    'content' => ['fr' => 'Profil', 'en' => 'Profile'],
                    'type' => 'title',
                ],
            ],
            '0aea62b2' => [
                'component' => 'object',
                'type' => 'editable-text',
                'config' => [
                    'label' => ['fr' => 'Titre'],
                    'placeholder' => ['fr' => 'Le titre'],
                    'help' => ['fr' => 'Ici le titre'],
                    'length' => 200,
                    'required' => true,
                    'tags' => ['sheet_title', 'sheet_data'],
                ],
            ],
            '0aea62b3' => [
                'component' => 'object',
                'type' => 'editable-text',
                'config' => [
                    'label' => ['fr' => 'Prénom'],
                    'placeholder' => ['fr' => 'Votre prénom'],
                    'help' => ['fr' => 'Ici le prénom'],
                    'length' => 200,
                    'required' => false,
                    'tags' => ['participant_firstname', 'participant_data'],
                ],
            ],
            '0aea62b4' => [
                'component' => 'object',
                'type' => 'editable-text',
                'config' => [
                    'label' => ['fr' => 'Nom'],
                    'placeholder' => ['fr' => 'Votre nom'],
                    'help' => ['fr' => 'Ici le nom'],
                    'length' => 200,
                    'required' => false,
                    'tags' => ['participant_lastname', 'participant_data'],
                ],
            ],
            '4b674gba' => [
                'component' => 'object',
                'type' => 'gender',
                'config' => [
                    'label' => ['fr' => 'Genre'],
                    'placeholder' => ['fr' => 'Genre'],
                    'help' => ['fr' => ''],
                    'required' => false,
                    'translatable' => false,
                    'tags' => ['participant_gender', 'participant_data'],
                ],
            ],
            'adc97e8d' => [
                'component' => 'object',
                'type' => 'nomenclature',
                'config' => [
                    'label' => ['fr' => 'Chiffre d\'affaires'],
                    'placeholder' => ['fr' => 'Votre Chiffre d\'affaires'],
                    'help' => ['fr' => 'Ici le Chiffre d\'affaires'],
                    'mode' => 'singles',
                    'nomenclature' => $nomenclatures['turnover'] ?? 1,
                    'tags' => ['sheet_organization_turnover', 'sheet_data'],
                ],
            ],
            '3ad4b72f' => [
                'component' => 'object',
                'type' => 'editable-text',
                'config' => [
                    'type' => 'text',
                    'label' => ['fr' => 'Nom (Société / Organisme)'],
                    'placeholder' => ['fr' => 'Nom (Société / Organisme)'],
                    'help' => ['fr' => ''],
                    'length' => 250,
                    'required' => true,
                    'translatable' => false,
                    'tags' => ['sheet_organization', 'sheet_title', 'sheet_data'],
                ],
            ],
            'd224f0e7' => [
                'component' => 'object',
                'type' => 'editable-text',
                'config' => [
                    'type' => 'text',
                    'label' => ['fr' => 'Ville'],
                    'placeholder' => ['fr' => 'Ville'],
                    'help' => ['fr' => ''],
                    'length' => 250,
                    'required' => false,
                    'translatable' => false,
                    'tags' => ['sheet_city', 'sheet_data'],
                ],
            ],
        ];
        if (isset($nomenclatures['position'])) {
            $children['6c4a3a4f'] = [
                'component' => 'object',
                'type' => 'nomenclature',
                'config' => [
                    'label' => ['fr' => 'Fonction'],
                    'placeholder' => ['fr' => 'Position'],
                    'mode' => 'singles',
                    'nomenclature' => $nomenclatures['position'],
                    'tags' => ['participant_position', 'participant_data'],
                ],
            ];
        }

        return $this->createBlock($children);
    }

    private function step2Block()
    {
        return $this->createBlock([
            'cb66008e' => [
                'component' => 'object',
                'type' => 'image',
                'config' => [
                    'label' => ['fr' => 'Photo'],
                    'placeholder' => ['fr' => 'Photo'],
                    'help' => ['fr' => ''],
                    'required' => false,
                    'tags' => ['participant_avatar', 'participant_data'],
                ],
            ],
            'dd66008e' => [
                'component' => 'object',
                'type' => 'upload',
                'config' => [
                    'label' => ['fr' => 'Upload de fichier'],
                    'placeholder' => ['fr' => 'Upload de fichier'],
                    'help' => ['fr' => ''],
                    'required' => false,
                    'crypted' => false,
                    'formats' => ['csv', 'image'],
                    'tags' => ['participant_data', 'sheet_data'],
                ],
            ],
        ], '12');
    }
}
