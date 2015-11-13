<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WhatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $participantTemplate = $this->getParticipantTemplate($options['who']);
        $sheetTemplate       = $this->getSheetTemplate($options['who']);

        $builder
            ->add('participant', new WhatCheckboxesType(), [
                'template' => $participantTemplate,
                'locale'   => $options['locale'],
            ])
            ->add('sheet', new WhatCheckboxesType(), [
                'template' => $sheetTemplate,
                'locale'   => $options['locale'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
        ]);

        $resolver->setRequired(['who', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'what';
    }

    private function getParticipantTemplate(WhoInterface $who)
    {
        if ($who instanceof Type) {
            return $who->getParticipantTemplate();
        }

        if ($who instanceof Category) {
            return [];
        }

        throw new \InvalidArgumentException();
    }

    private function getSheetTemplate(WhoInterface $who)
    {
        if ($who instanceof Type) {
            return $who->getSheetTemplate();
        }

        if ($who instanceof Category) {
            return [];
        }

        throw new \InvalidArgumentException();
    }
}
