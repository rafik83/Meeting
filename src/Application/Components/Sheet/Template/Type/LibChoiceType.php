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

class LibChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'placeholder' => false,
            'choices'     => [],
        ]);
    }

    /**
     * return array
     */
    public function getChoices()
    {
        return $this->getOption('choices');
    }

    /**
     * return array
     */
    public function getPlaceholder()
    {
        return $this->getOption('placeholder');
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }
}
