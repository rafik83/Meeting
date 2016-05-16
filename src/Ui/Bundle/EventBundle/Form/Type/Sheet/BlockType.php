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
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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

        foreach ($block->getEditableObjects() as $key => $object) {
            if ($object instanceof Template\Object\EditableText) {
                $this->addText($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Nomenclature) {
                $this->addNomenclature($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Image) {
                $this->addImage($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Telephone) {
                $this->addTelephone($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Country) {
                $this->addCountry($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Url) {
                $this->addUrl($key, $builder, $object, $options['locale']);
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
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addText($key, FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $attr = $object->getOption('length') ? ['maxlength' => $object->getOption('length')] : [];

        $builder->add($key, TextType::class, [
            'label'       => false,
            'placeholder' => $object->getOption('placeholder')[$locale],
            'required'    => $object->getOption('required'),
            'attr'        => $attr,
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addImage($key, FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($key, FileType::class, [
            'label'    => false,
            'required' => $object->getOption('required'),
            'mapped'   => false,
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addTelephone($key, FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($key, TelephoneType::class, [
            'label'       => false,
            'required'    => $object->getOption('required'),
            'placeholder' => $object->getOption('placeholder')[$locale],
            'attr'        => [
                'class' => 'telephone-intl-input',
            ]
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object\Url  $url
     * @param string               $locale
     */
    private function addUrl($key, FormBuilderInterface $builder, Template\Object\Url $url, $locale)
    {
        $builder->add($key, UrlType::class, [
            'label'       => false,
            'required'    => $url->getOption('required'),
            'placeholder' => $url->getOption('placeholder')[$locale],
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addCountry($key, FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($key, CountryType::class, [
            'label'       => false,
            'required'    => $object->getOption('required'),
            'placeholder' => $object->getOption('placeholder')[$locale],
            'attr'        => [
                'class'            => 'form-control select2',
                'data-placeholder' => $object->getOption('label')[$locale],
            ],
        ]);
    }

    /**
     * @param string                       $key
     * @param FormBuilderInterface         $builder
     * @param Template\Object\Nomenclature $object
     * @param string                       $locale
     */
    private function addNomenclature(
        $key,
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

        $builder->add($key, ChoiceType::class, [
            'label'       => false,
            'required'    => $object->getOption('required'),
            'choices'     => $choices,
            'placeholder' => $object->getOption('label')[$locale],
            'attr'        => [
                'class'            => 'form-control select2',
                'data-placeholder' => $object->getOption('label')[$locale],
            ],
        ]);
    }
}
