<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Block $block */
        $block = $options['block'];

        /** @var Object $object */
        foreach ($block->getObjects() as $key => $object) {
            if ($object instanceof Object\EditableText) {
                $builder->add($key, TextType::class, [
                    'label'       => false,
                    'placeholder' => $object->getOption('placeholder')[$options['locale']],
                    'required'    => $object->getOption('required'),
                ]);
            } elseif ($object instanceof Object\Nomenclature) {
                $builder->add($key, TextType::class, [
                    'label'       => false,
                    'placeholder' => $object->getOption('placeholder')[$options['locale']],
                    'required'    => $object->getOption('required'),
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
        $resolver->setRequired(['block', 'locale']);
        $resolver->setAllowedTypes('block', Block::class);
    }
}
