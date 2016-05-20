<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('translations', CollectionType::class, [
                'entry_type' => PackageTranslationsType::class,
                'label'      => false,
            ])
            ->add('availabilityCurrent', IntegerType::class, [
                'required' => false,
            ])
            ->add('availabilityMax', IntegerType::class, [
                'required' => false,
            ])
            ->add('unitPrice', NumberType::class)
            ->add('features', CollectionType::class, [
                'entry_type'    => FeatureType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => [
                    'label' => false,
                    'event' => $options['event'],
                ],
            ])
            ->add('participantIncluded', IntegerType::class, [
                'required' => true,
                'attr'     => [
                    'min' => 0,
                ]
            ])
            ->add('file', FileType::class, [
                'required' => false,
            ])
            ->add('productIncluded', CollectionType::class, [
                'entry_type'    => ProductIncludedType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => [
                    'label' => false,
                    'event' => $options['event'],
                ],
                'attr' => [
                    'data-collection-product-included' => 'data-collection-product-included',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'package_create';
    }
}
