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

class CreateForEventHandler
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
     * OrganizerCreateHandler constructor.
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
     * @param CreateForEvent $create
     *
     * @return CreateResult
     */
    public function handle(CreateForEvent $create)
    {
        $template = new Template($create->title, [], $create->event->getLocales(), $this->dateTime);
        $template->setEvent($create->event);

        $this->templateRepository->add($template);

        return new CreateResult($template);
    }
}
