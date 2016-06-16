<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Application\Command\Event\Update;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventUpdateType extends AbstractType
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
            ->add('fallback', LocaleType::class, [
                'preferred_choices' => $prefered,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => EventUpdateTranslationType::class,
                'label'      => false,
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales', 'currentLocale']);
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }
}
