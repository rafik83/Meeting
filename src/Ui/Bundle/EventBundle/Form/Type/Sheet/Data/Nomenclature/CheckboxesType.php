<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature\ItemsToCheckboxTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckboxesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ItemsToCheckboxTransformer($options['nomenclature']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'nomenclature', 'levelsArchitecture']);
        $resolver->setDefaults([
            'choices'                   => function (Options $options) {
                return $options['nomenclature']->getLastLevel();
            },
            'expanded'                  => true,
            'multiple'                  => true,
            'choice_translation_domain' => false,
            'choice_name'               => function (NomenclatureItem $item = null) {
                return null === $item ? null : $item->getCleanKey();
            },
            'choice_value'              => function (NomenclatureItem $item = null) {
                return null === $item ? null : mb_strtolower($item->getKey());
            },
            'choice_label'              => function (Options $options) {
                return function (NomenclatureItem $item) use ($options) {
                    return $item->getLabel($options['locale']);
                };
            },
            'levelsArchitecture' => []
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['locale'] = $options['locale'];
        $view->vars['nomenclature'] = $options['nomenclature'];
        $view->vars['levelsArchitecture'] = $options['levelsArchitecture'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_checkboxes';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
