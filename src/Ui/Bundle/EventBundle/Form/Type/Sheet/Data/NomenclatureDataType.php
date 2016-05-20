<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Template\Object;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\CheckboxesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\SinglesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class NomenclatureDataType extends AbstractType
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * NomenclatureDataType constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['object'] instanceof Object\Nomenclature) {
            throw new \Exception('Nomenclature object expected.');
        }

        $nomenclature = $this->nomenclatureRepository->findById($options['object']->getNomenclatureId());

        if ($options['object']->isSingles()) {
            $this->addSingles($nomenclature, $builder, $options);
        } elseif ($options['object']->isRadios()) {
            $this->addRadios($nomenclature, $builder, $options);
        } elseif ($options['object']->isCheckboxes()) {
            $this->addCheckboxes($nomenclature, $builder, $options);
        } else {
            throw new \Exception('Not implemented yet.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'label'       => false,
            'data_class'  => Object\Nomenclature::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_data';
    }

    /**
     * @param Nomenclature         $nomenclature
     * @param FormBuilderInterface $form
     * @param array                $options
     *
     * @throws \Exception
     */
    private function addRadios(Nomenclature $nomenclature, FormBuilderInterface $form, array $options)
    {
        $form->add('item', ChoiceType::class, [
            'choices'                   => $nomenclature->getLastLevel(),
            'expanded'                  => true,
            'multiple'                  => false,
            'choice_translation_domain' => false,
            'choices_as_values'         => true,
            'choice_label'              => function (NomenclatureItem $item) use ($options) {
                return $item->getLabel($options['locale']);
            },
        ]);
    }

    /**
     * @param Nomenclature         $nomenclature
     * @param FormBuilderInterface $form
     * @param array                $options
     *
     * @throws \Exception
     */
    private function addCheckboxes(Nomenclature $nomenclature, FormBuilderInterface $form, array $options)
    {
        $form->add('items', CheckboxesType::class, [
            'nomenclature' => $nomenclature,
            'locale'       => $options['locale'],
        ]);
    }

    /**
     * @param Nomenclature         $nomenclature
     * @param FormBuilderInterface $form
     * @param array                $options
     *
     * @throws \Exception
     */
    private function addSingles(Nomenclature $nomenclature, FormBuilderInterface $form, array $options)
    {
        $form->add('item', SinglesType::class, [
            'nomenclature' => $nomenclature,
            'locale'       => $options['locale'],
            'label'        => false,
        ]);
    }
}
