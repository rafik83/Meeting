<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetTemplateChoiceType extends AbstractType
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(TemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'events'           => [],
            'class'            => Template::class,
            'choices'          => function (Options $options) {
                return $this->getResults($options);
            },
            'choice_label'     => 'title',
            'repositoryMethod' => function (TemplateRepositoryInterface $templateRepository) {
                return $templateRepository->getBaseTemplate();
            },
            'repositoryMethodOrganizer' => function (Options $options) {
                return function (TemplateRepositoryInterface $templateRepository) use ($options) {
                    return $templateRepository->getTemplateForGivenEvents($options['events']);
                };
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @param Options $options
     * @return array
     */
    private function getResults(Options $options)
    {
        $baseTemplates      = $options['repositoryMethod']($this->templateRepository);
        $organizerTemplates = $options['repositoryMethodOrganizer']($this->templateRepository);

        $templates = [];

        empty($baseTemplates)      ? :$templates['form.type_template.sheet.base'] = $baseTemplates;
        empty($organizerTemplates) ? :$templates['form.type_template.sheet.organizer'] = $organizerTemplates;

        return $templates;
    }
}
