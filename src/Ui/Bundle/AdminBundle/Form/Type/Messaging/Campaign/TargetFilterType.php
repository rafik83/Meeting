<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\CategoryChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\AbstractFilterType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter form used to select sheets of an event as targets of a messaging campaign (emailing).
 */
class TargetFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var Event $event */
        $event = $options['event'];

        $booleanFilters = $this->booleanFilterBuilder->getFilters($event);

        if (!empty($booleanFilters)) {
            $builder->add('boolean_filters', ChoiceType::class, [
                'choices'            => array_flip($booleanFilters),
                'label'              => 'admin.sheet.filter.template_filters',
                'required'           => false,
                'multiple'           => true,
                'expanded'           => true,
                'translation_domain' => 'messages',
            ]);
        }

        $categories = $this->categoryRepository->getCategoriesByEvent($event, $options['locale']);

        if (!empty($categories)) {
            $builder->add('category', CategoryChoiceType::class, [
                'label'    => 'form.sheet_filter.children.category.label',
                'event'    => $event,
                'locale'   => $options['locale'],
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->addAllowedTypes('event', Event::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'targetting';
    }
}
