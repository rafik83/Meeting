<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

interface TemplateRepositoryInterface
{
    /**
     * @return SheetTemplate[]
     */
    public function all();

    /**
     * @return SheetTemplate[]
     */
    public function getBaseTemplate();

    /**
     * @param array $events
     *
     * @return SheetTemplate[]
     */
    public function getTemplateForGivenEvents(array $events);

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
}
