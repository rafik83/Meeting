<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TipTranslationType extends AbstractType
{
    /**
     * @var array
     */
    private $preferredLocales;

    /**
     * TipTranslationType constructor.
     *
     * @param array $preferredLocales
     */
    public function __construct(array $preferredLocales)
    {
        $this->preferredLocales = $preferredLocales;
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('locale', LocaleType::class, [
                'preferred_choices' => $this->preferredLocales,
            ])
            ->add('content', TextareaType::class);
    }
    
   
    
    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_translation';
    }
}
