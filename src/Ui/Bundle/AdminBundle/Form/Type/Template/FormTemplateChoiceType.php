<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template;

use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTemplateChoiceType extends AbstractType
{
    /** @var FormTemplateRepositoryInterface */
    private $templateRepository;

    public function __construct(FormTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('event')
            ->setDefaults([
                'choices'          => function (Options $options) {
                    return $this->getResults($options);
                },
                'choice_label'     => 'title',
                'repositoryMethod' => function (FormTemplateRepositoryInterface $templateRepository) {
                    return $templateRepository->getBaseTemplates();
                },
                'repositoryMethodEvent' => function (Options $options) {
                    return function (FormTemplateRepositoryInterface $templateRepository) use ($options) {
                        return $templateRepository->findByEvent($options['event']);
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
     *
     * @return array
     */
    private function getResults(Options $options): array
    {
        $baseTemplates  = $options['repositoryMethod']($this->templateRepository);
        $eventTemplates = $options['repositoryMethodEvent']($this->templateRepository);

        $templates = [];

        if (!empty($baseTemplates)) {
            $templates['form.type_template.registration.base'] = $baseTemplates;
        }

        if (!empty($eventTemplates)) {
            $templates['form.type_template.registration.event'] = $eventTemplates;
        }

        return $templates;
    }
}
