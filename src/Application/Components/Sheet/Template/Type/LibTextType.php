<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class LibTextType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'translationRequired' => false,
                'translatable'        => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isTranslatable()
    {
        return (bool) $this->getOption('translatable');
    }

    /**
     * {@inheritdoc}
     */
    public function isTranslationRequired()
    {
        return (bool) $this->getOption('translationRequired');
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }
}
