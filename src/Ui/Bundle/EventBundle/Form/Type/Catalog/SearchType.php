<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    /** @var CommandBus */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheridoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event     = $options['event'];
        $locale    = $options['locale'];
        $typeViews = $options['typeViews'];

        $builder
            ->add('orderBy', ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ]);

        // show type facette only if there is more than one filter
        if (count($typeViews) > 1) {
            $builder
                ->add(
                    'type',
                    ChoiceType::class,
                    [
                        'label'        => 'form.search.type.label',
                        'expanded'     => true,
                        'multiple'     => true,
                        'choices'      => $typeViews,
                        'choice_value' => function (TypeView $typeView) {
                            return $typeView->id;
                        },
                        'choice_label' => function (TypeView $typeView) {
                            return $typeView->title;
                        },
                    ]
                );
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $formEvent) use ($event, $locale) {
                $data = $formEvent->getData();
                $this->formModifierByType($event, $locale, $formEvent->getForm(), $data['type']);
            }
        );

        $builder->get('type')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $formEvent) use ($event, $locale) {
                $this->formModifierByType(
                    $event,
                    $locale,
                    $formEvent->getForm()->getParent(),
                    $formEvent->getForm()->getData()
                );
            }
        );

        // Set a higher priority to avoid ValidationListener
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $formEvent) {
            $formEvent->stopPropagation();
        }, 900);
    }

    /**
     * {@inheridoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'typeViews']);
        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_search';
    }

    /**
     * @param Event         $event
     * @param string        $locale
     * @param FormInterface $form
     * @param array         $typeViews
     */
    private function formModifierByType(Event $event, $locale, FormInterface $form, $typeViews)
    {
        $organizationCategoryViews = $this->commandBus->handle(
            new OrganizationCategoryViewQuery($event, ['type' => $typeViews], $locale)
        );

        $form->add('organizationCategory', ChoiceType::class, [
            'choices'      => $organizationCategoryViews,
            'choice_value' => function (OrganizationCategoryView $organizationCategoryView = null) {
                if ($organizationCategoryView !== null) {
                    return $organizationCategoryView->key;
                }

                return null;
            },
            'choice_label' => function (OrganizationCategoryView $organizationCategoryView = null) {
                if ($organizationCategoryView !== null) {
                    return $organizationCategoryView->title;
                }

                return null;
            },
            'required'     => false,
            'multiple'     => true,
            'attr'         => [
                'class' => 'form-control select2',
            ],
        ]);
    }
}
