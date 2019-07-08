<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\PromotionType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\TranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('prefix', TextType::class, ['required' => false])
            ->add('number', IntegerType::class, ['required' => true])
            ->add('stock', IntegerType::class, ['required' => false])
            ->add(
                'validUntil',
                DateTimePickerType::class,
                [
                    'required' => false,
                    'view_timezone' => $options['event']->getTimeZone(),
                ]
            )
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'locales' => $options['event']->getLocales(),
                    'entry_type' => TranslationType::class,
                    'entry_options' => [],
                    'label' => false,
                ]
            )
            ->add(
                'promotions',
                CollectionType::class,
                [
                    'entry_type' => PromotionType::class,
                    'entry_options' => [
                        'event' => $options['event'],
                        'locale' => $options['locale'],
                        'label' => false,
                        'error_bubbling' => false,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'error_bubbling' => false,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }
}
