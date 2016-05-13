<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\CountryDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextInputDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\NomenclatureDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\TelephoneDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\UrlDataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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

        foreach ($block->getEditableObjects() as $object) {
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
            } elseif ($object instanceof Template\Object\Url) {
                $this->addUrl($builder, $object, $options['locale']);
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
        $builder->add($object->getKey(), EditableTextInputDataType::class, [
            'object' => $object,
            'locale' => $locale,
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
        $builder->add($object->getKey(), TelephoneDataType::class, [
            'object' => $object,
            'locale' => $locale,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Template\Object\Url  $url
     * @param string               $locale
     */
    private function addUrl(FormBuilderInterface $builder, Template\Object\Url $url, $locale)
    {
        $builder->add($url->getKey(), UrlDataType::class, [
            'object' => $url,
            'locale' => $locale,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addCountry(FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($object->getKey(), CountryDataType::class, [
            'object' => $object,
            'locale' => $locale,
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

        $builder->add($object->getKey(), NomenclatureDataType::class, [
            'object' => $object,
            'locale' => $locale
        ]);
    }
}
