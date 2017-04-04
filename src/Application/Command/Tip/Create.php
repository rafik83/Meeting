<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip;

class Create
{
    /** @var string */
    public $title;
    
    /** @var bool */
    public $onMeetingManagement;
    
    /** @var bool */
    public $onCatalog;
    
    /** @var bool */
    public $onPrintPlanning;
    
    /** @var array */
    public $translations;

    /** @var \DateTimeInterface */
    public $dateTime;

    /**
     * Create constructor.
     *
     * @param array $defaultLocales
     */
    public function __construct(array $defaultLocales)
    {
        $this->dateTime = new \DateTime();

        foreach ($defaultLocales as $locale) {
            $this->translations[] = ['locale' => $locale];
        }
    }
}
