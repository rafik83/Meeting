<?php

namespace Proximum\Vimeet\Domain\Repository\Template;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

interface SheetTemplateRepositoryInterface
{
    /**
     * @return SheetTemplate[]
     */
    public function all();

    /**
     * @param int $id
     *
     * @return null|SheetTemplate
     */
    public function findById($id);

    /**
     * @param array $events
     *
     * @return SheetTemplate[]
     */
    public function getTemplateForGivenEvents(array $events);

    /**
     * @param Event $event
     *
     * @return SheetTemplate[]
     */
    public function getTemplateForGivenEvent(Event $event);

    /**
     * @return SheetTemplate[]
     */
    public function getBaseTemplates();

    /**
     * @param array $events
     * @param array $filters
     *
     * @return SheetTemplate[]
     */
    public function getOrganizerTemplates(array $events, array $filters);

    /**
     * @param SheetTemplate $template
     */
    public function add(SheetTemplate $template);

    /**
     * @param SheetTemplate $template
     */
    public function set(SheetTemplate $template);

    /**
     * @return SheetTemplate[]
     */
    public function getUsedTemplateForGivenEvent(Event $event): array;
}
