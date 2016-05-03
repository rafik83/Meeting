<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Library\TelephoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
        /** @var Template\Block $block */
        $block = $options['block'];

        foreach ($block->getObjects() as $object) {
            if ($object instanceof Template\Object\EditableText) {
                $this->addText($builder, $object, $options['locale']);

            } elseif ($object instanceof Template\Object\Nomenclature) {
                $this->addNomenclature($builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Image) {
                $this->addImage($builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Telephone) {
                $this->addTelephone($builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Country) {
                $this->addCountry($builder, $object, $options['locale']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Template\Block::class]);
        $resolver->setRequired(['event', 'block', 'locale']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('block', Template\Block::class);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addText(FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $attr = $object->getOption('length') ? ['maxlength' => $object->getOption('length')] : [];

        $builder->add($object->getKey(), TextType::class, [
            'label'       => false,
            'placeholder' => $object->getOption('placeholder')[$locale],
            'required'    => $object->getOption('required'),
            'attr'        => $attr,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addImage(FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($object->getKey(), FileType::class, [
            'label'    => false,
            'required' => $object->getOption('required'),
            'mapped'   => false,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addTelephone(FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($object->getKey(), TelephoneType::class, [
            'label'       => false,
            'required'    => $object->getOption('required'),
            'placeholder' => $object->getOption('placeholder')[$locale],
            'attr'        => [
                'class' => 'telephone-intl-input',
            ]
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addCountry(FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($object->getKey(), CountryType::class, [
            'label'       => false,
            'required'    => $object->getOption('required'),
            'placeholder' => $object->getOption('placeholder')[$locale],
        ]);
    }

    /**
     * @param FormBuilderInterface         $builder
     * @param Template\Object\Nomenclature $object
     * @param string                       $locale
     */
    private function addNomenclature(
        FormBuilderInterface $builder,
        Template\Object\Nomenclature $object,
        $locale
    ) {
        $choices = $object->getNomenclatureLabels();

        if (null === $choices) {
            return;
        }

        if (!is_array(array_shift($choices))) {
            $choices = array_flip($choices);
        } else {
            $choices = array_map(function ($values) {
                return array_flip($values);
            }, $choices);
        }

        if (true === $object->getOption('required')) {
            // Add an empty option in order to show the placeholder in select2
            $choices = array_merge(['' => ''], $choices);
        }

        $builder->add($object->getKey(), ChoiceType::class, [
            'label'    => false,
            'required' => $object->getOption('required'),
            'choices'  => $choices,
            'attr'     => [
                'class'            => 'form-control select2',
                'data-placeholder' => $object->getOption('label')[$locale],
            ],
        ]);
    }
}
