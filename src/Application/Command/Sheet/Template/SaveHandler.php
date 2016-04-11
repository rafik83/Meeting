<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class SaveHandler
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * SaveHandler constructor.
     *
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(TemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param Save $save
     */
    public function handle(Save $save)
    {
        $save->template->setValue($save->value);

        foreach ($save->template->getLocales() as $locale) {
            $save->template->addLocale($locale);
        }

        $this->templateRepository->set($save->template);
    }
}
