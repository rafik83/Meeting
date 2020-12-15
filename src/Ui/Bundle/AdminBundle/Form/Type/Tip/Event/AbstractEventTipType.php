<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractEventTipType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('onMeetingManagement', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onCatalog', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onPrintPlanning', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onSheet', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onAgenda', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onPackage', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onContacts', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onProgram', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onConfirmationPhone', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onNetworking', CheckboxType::class, [
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'  => TipEventTranslationType::class,
                'label'       => false,
            ])
            ->add('types', TypeChoiceType::class, [
                'event'    => $options['event'],
                'expanded' => true,
                'locale'   => $options['locale'],
                'multiple' => true,
                'user'     => $options['admin'],
                'required' => false,
            ])
            ->add('display', ChoiceType::class, [
                'choices' => Tip::DISPLAY_CHOICES,
                'choice_label' => static function ($item) {
                    return 'form.tip.children.display.' . $item;
                },
                'label' => 'form.tip.children.display.label',
                'expanded' => true,
                'required' => true,
            ])
            ->add('conditionHasCart', YesNoType::class, [
                'expanded' => true,
                'label' => 'form.tip.children.conditionHasCart.label',
                'required' => false,
            ])
            ->add('conditionOnOrders', ChoiceType::class, [
                'choices' => Tip::CONDITION_ON_ORDERS_CHOICES,
                'multiple' => true,
                'choice_label' => static function ($item) {
                    return 'form.tip.children.conditionOnOrders.' . $item;
                },
                'label' => 'form.tip.children.conditionOnOrders.label',
                'required' => false,
                'expanded' => true,
            ])
            ->add('conditionHasRemainingToPay', YesNoType::class, [
                'label' => 'form.tip.children.conditionHasRemainingToPay.label',
                'expanded' => true,
                'required' => false,
            ])
            ->add('conditionIsPhoneConfirmed', YesNoType::class, [
                'label' => 'form.tip.children.conditionIsPhoneConfirmed.label',
                'expanded' => true,
                'required' => false,
            ])
            ->add('conditionIsCompleteSheet', YesNoType::class, [
                'label' => 'form.tip.children.conditionIsCompleteSheet.label',
                'expanded' => true,
                'required' => false,
            ])
            ->add('conditionHasPendingMeetingProposition', YesNoType::class, [
                'label' => 'form.tip.children.conditionHasPendingMeetingProposition.label',
                'expanded' => true,
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'event',
            'locale',
            'admin',
        ]);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('locale', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(
                Intl::getLocaleBundle()->getLocaleName($translation->vars['name'])
            );
        }
    }
}
