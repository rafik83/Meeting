<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WhoType extends AbstractType
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * WhoType constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TypeRepositoryInterface     $typeRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->typeRepository     = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                return [
                    'Categorie' => $this->categoryRepository->getCategoriesByEvent($options['event']),
                    'Type'      => $this->typeRepository->getTypesByEvent($options['event']),
                ];
            },
            'choices_as_values' => true,
            'choice_label'      => function (Options $options) {
                return function ($choice) use ($options) {
                    if ($choice instanceof Type || $choice instanceof Category) {
                        $translation = $choice->getTranslations()->get($options['locale']);
                        if (null === $translation) {
                            $translation = $choice->getTranslations()->get($options['event']->getFallback());
                        }

                        return $translation->getTitle();
                    }

                    return (string) $choice;
                };
            },
            'choice_value' => function ($choice) {
                if ($choice instanceof Type) {
                    return sprintf('type:%s', $choice->getId());
                }

                if ($choice instanceof Category) {
                    return sprintf('category:%s', $choice->getId());
                }

                return '';
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
