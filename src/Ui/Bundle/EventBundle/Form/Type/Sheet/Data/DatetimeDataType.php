<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatetimeDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $datetime = $options['object'];
        $locale = $options['locale'];

        $builder
            ->add('date', DateTimePickerType::class, [
                'label' => $datetime->getOption('label', $locale),
                'required' => $datetime->getOption('required'),
                'help' => $datetime->getOption('help')[$locale],
                'translation_domain' => false,
                'display_hour' => $datetime->displayHours(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['object', 'locale'])
            ->setAllowedTypes('object', TemplateObject\DateTime::class)
            ->setDefaults([
                'data_class' => TemplateObject\DateTime::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'datetime_data';
    }
}
