<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SingleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'nomenclature']);
        $resolver->setDefaults([
            'placeholder' => function (Options $options) {
                return implode(', ', array_map(function (NomenclatureItem $item) use ($options) {
                    return $item->getLabel($options['locale']);
                }, \array_slice($options['choices'], 0, 3)));
            },
            'translation_domain' => false,
            'choice_translation_domain' => false,
            'choice_name' => function (NomenclatureItem $item = null) {
                return $item ? $item->getCleanKey() : null;
            },
            'choice_value' => function (NomenclatureItem $item = null) {
                return $item ? $item->getKey() : null;
            },
            'choice_label' => function (Options $options) {
                return function (NomenclatureItem $item) use ($options) {
                    return $item->getLabel($options['locale']);
                };
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['class'] = 'select2';
        $view->vars['attr']['data-placeholder'] = $options['placeholder'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
