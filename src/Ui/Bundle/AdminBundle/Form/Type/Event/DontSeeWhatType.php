<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated need to be rewrited
 */
class DontSeeWhatType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $participantTemplate = $this->getParticipantTemplate($options['who']);
        $sheetTemplate       = $this->getSheetTemplate($options['who']);

        $builder
            ->add('participant', WhatCheckboxesType::class, [
                'template' => $participantTemplate,
                'locale'   => $options['locale'],
            ])
            ->add('sheet', WhatCheckboxesType::class, [
                'template' => $sheetTemplate,
                'locale'   => $options['locale'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
        ]);

        $resolver->setRequired(['who', 'locale']);
    }

    /**
     * @param WhoInterface $who
     *
     * @return array
     */
    private function getParticipantTemplate(WhoInterface $who)
    {
        if ($who instanceof Type) {
            return $who->getParticipantTemplate();
        }

        if ($who instanceof Category) {
            return $this->templatesIntersectRecursive(array_map(function (Type $type) {
                return $type->getParticipantTemplate();
            }, $who->getTypes()->toArray()));
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param WhoInterface $who
     *
     * @return array
     */
    private function getSheetTemplate(WhoInterface $who)
    {
        if ($who instanceof Type) {
            return $who->getSheetTemplate();
        }

        if ($who instanceof Category) {
            return $this->templatesIntersectRecursive(array_map(function (Type $type) {
                return $type->getSheetTemplate();
            }, $who->getTypes()->toArray()));
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param array $templates
     *
     * @return array
     */
    private function templatesIntersectRecursive(array $templates)
    {
        $intersect = null;

        foreach ($templates as $template) {
            $intersect = ($intersect === null ? $template : $this->arrayIntersectRecursive($intersect, $template));
        }

        return $intersect ?: [];
    }

    /**
     * @param array $one
     * @param array $another
     *
     * @return array
     */
    private function arrayIntersectRecursive(array $one, array $another)
    {
        $intersect = [];

        foreach ($one as $key => $value) {
            if (isset($another[$key])) {
                if (is_array($value) && $another[$key]) {
                    $intersect[$key] = $this->arrayIntersectRecursive($value, $another[$key]);
                } elseif ($value === $another[$key]) {
                    $intersect[$key] = $value;
                }
            }
        }

        return $intersect;
    }
}
