<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Template;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

interface RegistrationTemplateRepositoryInterface
{
    /**
     * @return RegistrationTemplate[]
     */
    public function getBaseTemplates();

    /**
     * @return RegistrationTemplate[]
     */
    public function getAllOrganizersTemplates();

    /**
     * @param array $events
     *
     * @return RegistrationTemplate[]
     */
    public function getTemplateForGivenEvents(array $events);

    /**
     * @param Event $event
     *
     * @return RegistrationTemplate[]
     */
    public function getTemplateForGivenEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return RegistrationTemplate[]
     */
    public function getUsedTemplateForGivenEvent(Event $event);

    /**
     * @param RegistrationTemplate $registrationTemplate
     */
    public function add(RegistrationTemplate $registrationTemplate);

    /**
     * @param RegistrationTemplate $registrationTemplate
     */
    public function set(RegistrationTemplate $registrationTemplate);
}
