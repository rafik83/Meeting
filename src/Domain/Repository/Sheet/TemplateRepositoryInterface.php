<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Template;

interface TemplateRepositoryInterface
{
    /**
     * @return Template[]
     */
    public function all();

    /**
     * @return Template[]
     */
    public function getBaseTemplate();

    /**
     * @param array $events
     *
     * @return Template[]
     */
    public function getTemplateForGivenEvents(array $events);

    /**
     * @param array $events
     * @param array $filters
     *
     * @return Template[]
     */
    public function listOrganizerTemplate(array $events, array $filters);

    /**
     * @param Template $template
     */
    public function add(Template $template);

    /**
     * @param Template $template
     */
    public function set(Template $template);
}
