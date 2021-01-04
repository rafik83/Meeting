<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnabledStateChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices'                   => [
                'form.sheet_filter.children.enabledState.enabled.label'  => true,
                'form.sheet_filter.children.enabledState.disabled.label' => false,
            ],
            'choice_translation_domain' => 'forms',
            'placeholder'               => 'form.sheet_filter.children.enabledState.all.label',
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_enabled_state_choice';
    }
}
