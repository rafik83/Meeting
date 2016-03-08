<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Row;

use Proximum\Vimeet\Application\Components\Template\Row;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractLib extends Row
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'translatable' => false,
        ]);

        $resolver->setAllowedTypes('translatable', ['bool']);
    }

    /**
     * @param mixed  $value
     * @param string $locale
     *
     * @return mixed
     */
    public function getDisplayableValue($value, $locale)
    {
        return is_array($value) && $this->isTranslatable() ?
            (isset($value[$locale]) ? $value[$locale] : null) :
            $value;
    }

    /**
     * @return bool
     */
    public function isTranslatable()
    {
        return $this->options['translatable'];
    }
}
