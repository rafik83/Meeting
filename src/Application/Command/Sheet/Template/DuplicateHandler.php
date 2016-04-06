<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class DuplicateHandler
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * DuplicateHandler constructor.
     *
     * @param TemplateRepositoryInterface $templateRepository
     * @param \DateTimeInterface          $dateTime
     */
    public function __construct(TemplateRepositoryInterface $templateRepository, \DateTimeInterface $dateTime)
    {
        $this->templateRepository = $templateRepository;
        $this->dateTime           = $dateTime;
    }

    /**
     * @param Duplicate $duplicate
     *
     * @return DuplicateResult
     */
    public function handle(Duplicate $duplicate)
    {
        $template = new Template(
            $duplicate->title,
            $duplicate->template->getValue(),
            $duplicate->template->getLocales(),
            $this->dateTime
        );

        if (null !== $duplicate->event) {
            $template->setEvent($duplicate->event);
        }

        $this->templateRepository->add($template);

        return new DuplicateResult($template);
    }
}
