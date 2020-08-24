<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class RegistrationTemplateManager
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     */
    public function __construct(RegistrationTemplateRepositoryInterface $registrationTemplateRepository)
    {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
    }

    public function create(?Event $event): RegistrationTemplate
    {
        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [
                '211b2168' => [
                    'component' => 'block',
                    'type' => '8-4',
                    'config' => [],
                    'children' => [
                        [
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
                                    'label'=> ['fr' => 'Prénom'],
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
                                    'tags'=> ['participant_lastname', 'participant_data'],
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
                                    'nomenclature' => 1,
                                    'tags'=> ['sheet_organization_turnover', 'sheet_data'],
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
                                    'tags'=> ['sheet_organization', 'sheet_title', 'sheet_data'],
                                ],
                            ],

                        ],
                    ],
                ],
            ],
            $event ? $event->getLocales() : ['fr'],
            $event ? $event->getLocaleFallback() : 'fr',
            new \DateTime(),
            $event
        );

        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }
}
