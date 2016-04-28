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
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nomenclatures = $this->nomenclatureRepository->findByEvent($options['event']);

        /** @var Template\Block $block */
        $block = $options['block'];

        /** @var Template\Object $object */
        foreach ($block->getObjects() as $object) {
            if ($object instanceof Template\Object\EditableText) {
                $this->addText($builder, $object, $options['locale']);

            } elseif ($object instanceof Template\Object\Nomenclature) {
                $this->addNomenclature($builder, $object, $nomenclatures, $options['locale']);
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
     * @param Nomenclature[]       $nomenclatures
     * @param string               $locale
     */
    private function addNomenclature(
        FormBuilderInterface $builder,
        Template\Object $object,
        array $nomenclatures,
        $locale
    ) {
        $choices = $this->getChoicesFromNomenclature(
            $nomenclatures,
            $object->getOption('nomenclature'),
            $locale
        );

        if (null === $choices) {
            return;
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
        ) {
            return null;
        }

        /** @var Nomenclature $nomenclature */
        $nomenclature = $nomenclatures[$nomenclatureId];

        $items = $nomenclature->getValue();

        if (null === $items) {
            return null;
        }

        $choices = [];
        $depth = $nomenclature->getDepth();

        if (2 === $depth) {
            foreach ($items as $item) {
                if (!isset($item['children'])) {
                    continue;
                }

                $choices[$item['label'][$locale]] = array_flip(array_map(
                    function ($value) use ($locale) {
                        return $value['label'][$locale];
                    },
                    $item['children']
                ));
            }

            return $choices;

        } elseif (1 === $depth) {
            $choices = array_flip(array_map(function ($value) use ($locale) {
                return $value['label'][$locale];
            }, $items));

            return $choices;
        }

        return $choices;
    }
}
