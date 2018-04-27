<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class TranslatorAdapter implements TranslatorInterface
{
    /**
     * @var SymfonyTranslatorInterface
     */
    private $translator;

    /**
     * TranslatorAdapter constructor.
     *
     * @param SymfonyTranslatorInterface $translator
     */
    public function __construct(SymfonyTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }
}
