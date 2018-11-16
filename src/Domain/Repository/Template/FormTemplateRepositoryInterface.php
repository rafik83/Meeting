<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Template;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\Form\FormTemplateView;

interface FormTemplateRepositoryInterface
{
    /**
     * @return FormTemplate[]
     */
    public function findByEvent(Event $event): array;

    /**
     * @return FormTemplateView[]
     */
    public function getFormTemplateViewByType(Type $type, string $locale): array;
}
