<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractEventType extends AbstractType
{
    /**
     * @var array
     */
    private $supportedCurrencies;

    /**
     * @param array $supportedCurrencies
     */
    public function __construct(array $supportedCurrencies)
    {
        $this->supportedCurrencies = $supportedCurrencies;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prefered      = ['fr', 'en', 'es', 'de', 'it', 'zh'];
        $currentLocale = $options['currentLocale'];

        $builder
            ->add('title', TextType::class)
            ->add('locales', LocaleType::class, [
                'multiple'          => true,
                'preferred_choices' => $prefered,
            ])
            ->add('domain', TextType::class, [
                'placeholder' => 'form.event_create.children.domain.placeholder',
            ])
            ->add('timeZone', TimezoneType::class)
            ->add('fallback', LocaleType::class, [
                'preferred_choices' => $prefered,
            ])
            ->add('logo', FileType::class, [
                'required' => false,
                'attr'     => [
                    'accept' => implode(', ', ["image/jpeg", "image/pjpeg", "image/png", "image/x-png",]),
                ],
            ])
            ->add('country', CountryType::class)
            ->add('mode', VatModeType::class, [
                'expanded' => true,
            ])
            ->add('vat', NumberType::class)
            ->add('currency', CurrencyType::class, [
                'placeholder'  => 'form.event_update.children.currency.placeholder',
                'required'     => true,
                'choices'      => $this->supportedCurrencies,
                'choice_label' => function ($currentChoice) use ($currentLocale) {
                    return Intl::getCurrencyBundle()->getCurrencyName($currentChoice, $currentLocale);
                },
            ])
            ->add('leftColor', TextType::class)
            ->add('rightColor', TextType::class)
            ->add('textColor', TextType::class)
            ->add('organiserName', TextType::class, [
                'required'=> true
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['currentLocale']);
    }
}
