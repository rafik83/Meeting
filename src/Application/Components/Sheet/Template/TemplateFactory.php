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
            'lib_textarea'     => Type\LibTextAreaType::class,
            'lib_text'         => Type\LibTextType::class,
            'lib_participant'  => Type\LibParticipantType::class,
            'lib_planning'     => Type\LibPlanningType::class,
            'lib_email'        => Type\LibTextType::class,
            // Added row
            'added_row'        => Type\AddedRowType::class,
            // BC
            'choice_with_description' => Type\LibRadioType::class,
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
            if (!isset($groupTemplate['template'])) {
                return $this->createTemplateFromArray([
                    'default' => [
                        'label'    => 'Default',
                        'template' => $templateData,
                    ],
                ]);
            }

            $template->addGroup($this->createGroupFromArray($groupName, $groupTemplate));
        }

        return $template;
    }

    /**
     * @param string $typeName
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    public function createType($typeName)
    {
        $name = sha1(uniqid());
        $type = $this->getTypeInstance($name, $typeName);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $type->setOptions($resolver->resolve(['label' => [], 'type' => $typeName]));

        return $type;
    }

    /**
     * @param string $groupName
     * @param array  $templateData
     *
     * @return Group
     */
    private function createGroupFromArray($groupName, array $templateData)
    {
        $resolver = new OptionsResolver();
        $group    = new Group($groupName);
        $group->configureOptions($resolver);
        $group->setOptions($resolver->resolve($templateData));

        foreach ($templateData['template'] as $typeName => $typeTemplate) {
            $group->addType($this->createTypeFromArray($typeName, $typeTemplate));
        }

        return $group;
    }

    /**
     * @param string $typeName
     * @param array  $templateData
     *
     * @throws UnknownTypeException
     * @return TypeInterface
     *
     */
    private function createTypeFromArray($typeName, array $templateData)
    {
        $resolver = new OptionsResolver();

        $type     = $this->getTypeInstance($typeName, $templateData['type']);
        $type->configureOptions($resolver);
        $type->setOptions($resolver->resolve($templateData));

        return $type;
    }

    /**
     * @param string $typeName
     * @param string $type
     *
     * @throws UnknownTypeException
     * @return TypeInterface
     *
     */
    private function getTypeInstance($typeName, $type)
    {
        if (!isset($this->types[$type])) {
            throw new UnknownTypeException($type, array_keys($this->types));
        }

        return new $this->types[$type]($typeName);
    }
}
