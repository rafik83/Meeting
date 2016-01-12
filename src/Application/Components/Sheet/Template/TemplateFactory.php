<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownTypeException;
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
            'lib_choice'       => Type\LibChoiceType::class,
            'lib_country'      => Type\LibCountryType::class,
            'lib_first_name'   => Type\LibFirstNameType::class,
            'lib_last_name'    => Type\LibLastNameType::class,
            'lib_option'       => Type\LibOptionType::class,
            'lib_organization' => Type\LibOrganizationType::class,
            'lib_radio'        => Type\LibRadioType::class,
            'lib_textArea'     => Type\LibTextAreaType::class,
            'lib_text'         => Type\LibTextType::class,
        ];
    }

    /**
     * @param array $templateData
     *
     * @return Template
     */
    public function createTemplateFromArray(array $templateData)
    {
        $template = new Template();

        foreach ($templateData as $groupName => $groupTemplate) {
            $template->addGroup($groupName, $this->createGroupFromArray($groupTemplate));
        }

        return $template;
    }

    /**
     * @param array $templateData
     *
     * @return Group
     */
    private function createGroupFromArray(array $templateData)
    {
        $resolver = new OptionsResolver();
        $group    = new Group();
        $group->configureOptions($resolver);
        $group->setOptions($resolver->resolve($templateData));

        foreach ($templateData as $typeName => $typeTemplate) {
            $group->addType($typeName, $this->createTypeFromArray($typeTemplate));
        }

        return $group;
    }

    /**
     * @param array $templateData
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    private function createTypeFromArray(array $templateData)
    {
        $resolver = new OptionsResolver();
        $type     = $this->getTypeInstance($templateData['type']);
        $type->configureOptions($resolver);
        $type->setOptions($resolver->resolve($templateData));

        return $type;
    }

    /**
     * @param string $type
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    private function getTypeInstance($type)
    {
        if (!isset($this->types[$type])) {
            throw new UnknownTypeException($type, array_keys($this->types));
        }

        return new $this->types[$type]();
    }
}
