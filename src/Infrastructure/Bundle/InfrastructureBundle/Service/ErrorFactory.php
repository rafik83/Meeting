<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class ErrorFactory
{
    /**
     * @var SymfonyTranslatorInterface
     */
    private $translator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ErrorFactory constructor.
     *
     * @param SymfonyTranslatorInterface $translator
     * @param RequestStack               $requestStack
     */
    public function __construct(SymfonyTranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->translator   = $translator;
        $this->requestStack = $requestStack;
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
    public function create($messageKey, $locale = null, $domain = 'validators', $parameters = [], $pluralization = null)
    {
        if (null === $locale) {
            $locale = $this->requestStack->getMasterRequest()->getLocale();
        }

        return new FormError($this->translator->transChoice($messageKey, $pluralization, $parameters, $domain, $locale));
    }
}
