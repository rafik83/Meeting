<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Symfony\Component\Form\FormError;
use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class ErrorFactory
{
    /**
     * @var SymfonyTranslatorInterface
     */
    private $translator;

    /**
     * @param SymfonyTranslatorInterface $translator
     */
    public function __construct(SymfonyTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string   $messageKey
     * @param string   $locale
     * @param string   $domain
     * @param array    $parameters
     * @param int|null $pluralization
     *
     * @return FormError
     */
    public function create($messageKey, $locale, $domain = 'validators', $parameters = [], $pluralization = null)
    {
        return new FormError($this->translator->transChoice($messageKey, $pluralization, $parameters, $domain, $locale));
    }
}
