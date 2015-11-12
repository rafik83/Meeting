<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Proximum\Vimeet\Domain\Repository\NomenclatureItemRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;

class NomenclatureType extends AbstractLocalizedType
{
    /**
     * @var NomenclatureItemRepositoryInterface
     */
    private $nomenclatureItemRepository;

    /**
     * @param NomenclatureItemRepositoryInterface $nomenclatureItemRepository
     */
    public function __construct(NomenclatureItemRepositoryInterface $nomenclatureItemRepository)
    {
        $this->nomenclatureItemRepository = $nomenclatureItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];

        $choices = $this->nomenclatureItemRepository->getArrayOfNomenclatureItemsByNomenclatureId(
            $template['id'],
            $locale
        );

        $builder->add('value', 'choice', [
            'choices'     => $choices,
            'placeholder' => 'nomenclature.placeholder',
            'required'    => isset($template['required']) ? $template['required'] : true,
            'label'       => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lib_nomenclature';
    }
}
