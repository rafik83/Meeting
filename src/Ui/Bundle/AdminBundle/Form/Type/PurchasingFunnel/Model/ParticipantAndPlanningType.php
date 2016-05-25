<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel\Model;

use Proximum\Vimeet\Application\Command\PurchasingFunnel\Model\ParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantAndPlanningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('labels', CollectionType::class, [
                'entry_type' => TextType::class,
                'required'   => false,
            ])
            ->add('participant', ProductChoiceType::class, [
                'event'            => $options['event'],
                'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                    return $productRepository->findByEventAndTypes($options['event'], [Product::TYPE_OPTION]);
                },
            ])
            ->add('planning', ProductChoiceType::class, [
                'event'            => $options['event'],
                'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                    return $productRepository->findByEventAndTypes($options['event'], [Product::TYPE_OPTION]);
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => ParticipantAndPlanning::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['labels'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }
}
