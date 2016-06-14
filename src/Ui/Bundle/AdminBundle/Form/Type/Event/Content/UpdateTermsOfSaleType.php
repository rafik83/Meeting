<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content;

use Proximum\Vimeet\Application\Command\Event\Content\UpdateTermsOfSale;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateTermsOfSaleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', ContentTranslationLocaleType::class, [
                'content' => $options['content'],
                'label'   => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['content']);
        $resolver->setAllowedTypes('content', Content::class);
        $resolver->setDefaults([
            'data_class' => UpdateTermsOfSale::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'content_update_terms_of_sale';
    }
}
