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
            'class'            => Template::class,
            'choices'          => function (Options $options) {
                return $options['repositoryMethod']($this->templateRepository);
            },
            'choice_label'     => 'title',
            'repositoryMethod' => function (TemplateRepositoryInterface $templateRepository) {
                return $templateRepository->getBaseTemplate();
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
}
