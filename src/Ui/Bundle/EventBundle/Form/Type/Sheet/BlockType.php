<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                $choices = $this->getChoicesFromNomenclature(
                    $options['nomenclatures'],
                    $object->getOption('nomenclature'),
                    $options['locale']
                );

                if (null === $choices) {
                    continue;
                }

                $builder->add($key, ChoiceType::class, [
                    'label'    => false,
                    'required' => $object->getOption('required'),
                    'attr'     => [
                        'class'            => 'form-control select2',
                        'data-placeholder' => $object->getOption('label')[$options['locale']],
                    ],
                    'choices'  => $choices,
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
        $resolver->setRequired(['block', 'locale', 'nomenclatures']);
        $resolver->setAllowedTypes('block', Block::class);
    }

    /**
     * @param array  $nomenclatures
     * @param int    $nomenclatureId
     * @param string $locale
     *
     * @return null|array
     */
    private function getChoicesFromNomenclature(array $nomenclatures, $nomenclatureId, $locale)
    {
        if (!isset($nomenclatures[$nomenclatureId])
            || !$nomenclatures[$nomenclatureId] instanceof Nomenclature
            || !$nomenclature = $nomenclatures[$nomenclatureId]->getValue()
        ) {
            return null;
        }

        $choices = [];
        $depth = $nomenclatures[$nomenclatureId]->getDepth();

        if (2 === $depth) {
            foreach ($nomenclature as $item) {
                if (!isset($item['children'])) {
                    continue;
                }

                $choices[$item['label'][$locale]] = array_flip(
                    array_map(
                        function ($value) use ($locale) {
                            return $value['label'][$locale];
                        },
                        $item['children']
                    )
                );
            }

            return $choices;

        } elseif (1 === $depth) {
            $choices = array_flip(array_map(function ($value) use ($locale) {
                return $value['label'][$locale];
            }, $nomenclature));

            return $choices;
        }

        return $choices;
    }
}
