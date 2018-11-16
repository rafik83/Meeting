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
}
