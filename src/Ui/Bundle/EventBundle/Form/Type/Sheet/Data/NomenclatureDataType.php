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
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\SingleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data instanceof Object\Nomenclature) {
                throw new \Exception('Nomenclature object expected.');
            }

            $nomenclature = $this->nomenclatureRepository->findById($data->getNomenclatureId());

            if ($data->isSingles()) {
                $this->addSingles($nomenclature, $form, $options);
            } elseif ($data->isRadios()) {
                $this->addRadios($nomenclature, $form, $options);
            } elseif ($data->isCheckboxes()) {
                $this->addCheckboxes($nomenclature, $form, $options);
            } else {
                throw new \Exception('Not implemented yet.');
            }

        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale']);
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
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getData();

        if (!$data instanceof Object\Nomenclature) {
            throw new \Exception('Nomenclature object expected.');
        }

        if ($data->isSingles()) {
            // Put data-parent="" on select inputs
            $keys = array_keys($view->children);
            foreach ($keys as $index => $parent) {
                if (isset($keys[$index + 1])) {
                    $child = $keys[$index + 1];
                    $view->children[$child]->vars['attr']['data-parent'] = $view->children[$parent]->vars['id'];
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_data';
    }

    /**
     * @param Nomenclature  $nomenclature
     * @param FormInterface $form
     * @param array         $options
     *
     * @throws \Exception
     */
    private function addRadios(Nomenclature $nomenclature, FormInterface $form, array $options)
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
     * @param Nomenclature  $nomenclature
     * @param FormInterface $form
     * @param array         $options
     *
     * @throws \Exception
     */
    private function addCheckboxes(Nomenclature $nomenclature, FormInterface $form, array $options)
    {
        $form->add('items', CheckboxesType::class, [
            'nomenclature' => $nomenclature,
            'locale'       => $options['locale'],
        ]);
    }

    /**
     * @param Nomenclature  $nomenclature
     * @param FormInterface $form
     * @param array         $options
     *
     * @throws \Exception
     */
    private function addSingles(Nomenclature $nomenclature, FormInterface $form, array $options)
    {
        if ($nomenclature->getDepth() === 1) {
            $form
                ->add('item', SingleType::class, [
                    'choices'      => $nomenclature->getFirstLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'placeholder'  => $nomenclature->getTitle(),
                    'nomenclature' => $nomenclature,
                ])
            ;
        } elseif ($nomenclature->getDepth() === 2) {
            $form
                ->add('first', SingleType::class, [
                    'choices'      => $nomenclature->getFirstLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'mapped'       => false,
                    'nomenclature' => $nomenclature,
                ])
                ->add('item', SingleType::class, [
                    'choices'      => $nomenclature->getSecondLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'placeholder'  => $nomenclature->getTitle(),
                    'nomenclature' => $nomenclature,
                    'choice_attr'  => function (NomenclatureItem $item) {
                        return $item->getParent() ? ['data-parent' => $item->getParent()->getKey()] : [];
                    },
                ])
            ;
        } elseif ($nomenclature->getDepth() === 3) {
            $form
                ->add('first', SingleType::class, [
                    'choices'      => $nomenclature->getFirstLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'mapped'       => false,
                    'nomenclature' => $nomenclature,
                ])
                ->add('second', SingleType::class, [
                    'choices'      => $nomenclature->getSecondLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'mapped'       => false,
                    'nomenclature' => $nomenclature,
                    'choice_attr' => function (NomenclatureItem $item) {
                        return $item->getParent() ? ['data-parent' => $item->getParent()->getKey()] : [];
                    },
                ])
                ->add('item', SingleType::class, [
                    'choices'      => $nomenclature->getThirdLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'placeholder'  => $nomenclature->getTitle(),
                    'nomenclature' => $nomenclature,
                    'choice_attr'  => function (NomenclatureItem $item) {
                        return $item->getParent() ? ['data-parent' => $item->getParent()->getKey()] : [];
                    },
                ])
            ;
        } else {
            throw new \Exception(sprintf('Not implementation for depth of %s', $nomenclature->getDepth()));
        }
    }
}
