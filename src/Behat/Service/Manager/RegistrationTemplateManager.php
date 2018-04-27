<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param Event $event
     *
     * @return RegistrationTemplate
     */
    public function create(Event $event)
    {
        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [
                '211b2168' => [
                    'component' => 'block',
                    'type'      => '8-4',
                    'config'    => [],
                    'children'  => [
                        [
                            '0aea62b2' => [
                                'component' => 'object',
                                'type'      => 'editable-text',
                                'config'    => [
                                    'label'       => ['fr' => 'Titre'],
                                    'placeholder' => ['fr' => 'Le titre'],
                                    'help'        => ['fr' => 'Ici le titre'],
                                    'length'      => 200,
                                    'required'    => true,
                                    'tags'        => ['sheet_title', 'sheet_data'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $event->getLocales(),
            $event->getFallback(),
            new \DateTime(),
            $event
        );

        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }
}
