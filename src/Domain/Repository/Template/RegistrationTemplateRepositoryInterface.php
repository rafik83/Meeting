<?php

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
     * @param int $id
     *
     * @return null|RegistrationTemplate
     */
    public function findById($id);

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
