<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\UnknownTypeException;
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
     * @param array $arrayTemplate
     *
     * @return Template
     */
    public function createTemplateFromArray(array $arrayTemplate)
    {
        $template = new Template();

        foreach ($arrayTemplate as $groupName => $groupTemplate) {
            $template->addGroup($groupName, $this->createGroupFromArray($groupTemplate));
        }

        return $template;
    }

    /**
     * @param array $arrayTemplate
     *
     * @return Group
     */
    private function createGroupFromArray(array $arrayTemplate)
    {
        $group = new Group();

        foreach ($arrayTemplate as $typeName => $typeTemplate) {
            $group->addType($typeName, $this->createTypeFromArray($typeTemplate));
        }

        return $group;
    }

    /**
     * @param array $arrayTemplate
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    private function createTypeFromArray(array $arrayTemplate)
    {
        $resolver = new OptionsResolver();
        $type     = $this->getTypeInstance($arrayTemplate['type']);
        $type->configureOptions($resolver);
        $type->setOptions($resolver->resolve($arrayTemplate));

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
