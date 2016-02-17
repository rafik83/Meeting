<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\InvalidTypeException;
use Proximum\Vimeet\Application\Components\Template\Exception\NoTypeDefinedException;
use Proximum\Vimeet\Application\Components\Template\Row\LibTextRow;
use Proximum\Vimeet\Application\Components\Template\Row\LibEmailRow;
use Proximum\Vimeet\Application\Components\Template\Row\LibTextAreaRow;
use Proximum\Vimeet\Application\Components\Template\Row\LibChoiceRow;
use Proximum\Vimeet\Application\Components\Template\Row\LibCountryRow;
use Proximum\Vimeet\Application\Components\Template\Row\ProductOptionRow;
use Proximum\Vimeet\Application\Components\Template\Row\ProductPlaningRow;
use Proximum\Vimeet\Application\Components\Template\Row\ProductParticipantRow;
use Proximum\Vimeet\Application\Components\Template\Row\ProductRadioRow;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateFactory
{
    /**
     * @var array
     */
    private $types = [];

    /**
     * TemplateFactory constructor.
     */
    public function __construct()
    {
        $this->types = [
            // Lib
            'lib_text'        => LibTextRow::class,
            'lib_email'       => LibEmailRow::class,
            'lib_textarea'    => LibTextAreaRow::class,
            'lib_choice'      => LibChoiceRow::class,
            'lib_country'     => LibCountryRow::class,
            // Product
            'lib_option'      => ProductOptionRow::class,
            'lib_participant' => ProductParticipantRow::class,
            'lib_planning'    => ProductPlaningRow::class,
            'lib_radio'       => ProductRadioRow::class,
            // BC
            'choice_with_description' => ProductRadioRow::class,
        ];
    }

    /**
     * @param array $templatesArray
     *
     * @return Templates
     */
    public function createTemplatesFromArray(array $templatesArray)
    {
        return new Templates(array_map(function ($templateArray) {
            $resolver = new OptionsResolver();
            $resolver->setRequired(['label', 'template']);
            $resolver->setDefaults(['description' => '', 'position' => 0]);
            $resolver->setAllowedTypes('label', ['string', 'array']);
            $resolver->setAllowedTypes('description', ['string', 'array']);
            $resolver->setAllowedTypes('position', ['int']);
            $resolver->setAllowedTypes('template', ['array']);

            $options = $resolver->resolve($templateArray);

            return $this->createTemplateFromArray(
                $options['template'],
                $options['label'],
                $options['description'],
                $options['position']
            );
        }, $templatesArray));
    }

    /**
     * @param array  $templateArray
     * @param string $label
     * @param string $description
     * @param int    $position
     *
     * @return Template
     */
    public function createTemplateFromArray(array $templateArray, $label = '', $description = '', $position = 0)
    {
        return new Template($label, $description, $position, array_map(function (array $rowArray) {
            return $this->createRowFromArray($rowArray);
        }, $templateArray));
    }

    /**
     * @param array $arrayTemplate
     *
     * @return Row
     * @throws NoTypeDefinedException
     * @throws InvalidTypeException
     */
    public function createRowFromArray(array $arrayTemplate)
    {
        if (!isset($arrayTemplate['type'])) {
            throw new NoTypeDefinedException();
        }

        $type = $arrayTemplate['type'];

        if (!isset($this->types[$type])) {
            throw new InvalidTypeException($type, array_keys($this->types));
        }

        $class = $this->types[$type];

        return new $class($arrayTemplate);
    }
}
