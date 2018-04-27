<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductChoiceType::class, [
                'select2'      => true,
                'placeholder'  => '',
                'event'        => $options['event'],
                'locale'       => $options['locale'],
                'group_by'     => function (Product $product) {
                    return sprintf('form.product_choice.group_by.type.%s', $product->getType());
                },
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '',
                'choices'     => [
                    'form.promotion_code_promotion.children.type.percentOff' => Promotion::TYPE_PERCENT_OFF,
                    'form.promotion_code_promotion.children.type.valueOff'   => Promotion::TYPE_VALUE_OFF,
                    'form.promotion_code_promotion.children.type.free'       => Promotion::TYPE_FREE,
                ],
            ])
            ->add('value', NumberType::class, [
                'required' => false,
            ])
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'promotion_code_promotion';
    }
}
