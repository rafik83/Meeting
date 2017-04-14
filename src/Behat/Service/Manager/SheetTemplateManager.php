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
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SheetTemplateManager
{
    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    /**
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     */
    public function __construct(SheetTemplateRepositoryInterface $sheetTemplateRepository)
    {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
    }

    /**
     * @param Event $event
     *
     * @return SheetTemplate
     */
    public function create(Event $event)
    {
        $sheetTemplate = new SheetTemplate(
            'SheetTemplate',
            [],
            $event->getLocales(),
            $event->getFallback(),
            new \DateTime(),
            [],
            $event
        );

        $this->sheetTemplateRepository->add($sheetTemplate);

        return $sheetTemplate;
    }
}
