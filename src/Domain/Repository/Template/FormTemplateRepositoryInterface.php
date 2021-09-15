<?php

namespace Proximum\Vimeet\Domain\Repository\Template;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\Form\FormTemplateView;

interface FormTemplateRepositoryInterface
{

    public function add(FormTemplate $template): void;

    public function update(FormTemplate $template): void;

    /**
     * @param Event $event
     *
     * @return FormTemplate[]
     */
    public function findByEvent(Event $event): array;

    /**
     * @return FormTemplateView[]
     */
    public function getPublishedFormTemplateViewByType(Type $type, string $locale): array;

    public function getById(int $formTemplateId): ?FormTemplate;
}
