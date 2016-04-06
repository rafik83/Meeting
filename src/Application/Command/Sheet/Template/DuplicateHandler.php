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

class DuplicateHandler
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * DuplicateHandler constructor.
     *
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(TemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param Duplicate $duplicate
     *
     * @return DuplicateResult
     */
    public function handle(Duplicate $duplicate)
    {
        $template = $duplicate->template->duplicate($duplicate->title, $duplicate->createdAt);

        if (null !== $duplicate->event) {
            $template->setEvent($duplicate->event);
        }

        $this->templateRepository->add($template);

        return new DuplicateResult($template);
    }
}
