<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\Import;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CharsetChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TypeChoiceType::class, [
                'event'    => $options['event'],
                'locale'   => $options['locale'],
                'user'     => $options['user'],
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'required' => true,
            ])
            ->add('charset', CharsetChoiceType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('user', Admin::class);

        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'participant_import';
    }
}
