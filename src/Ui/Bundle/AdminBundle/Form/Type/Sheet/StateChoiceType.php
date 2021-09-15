<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StateChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices'                   => Sheet::getAllStates(),
            'choice_translation_domain' => 'messages',
            'choice_label'              => function ($currentChoice) {
                return sprintf('event.sheet.state.%s', $currentChoice);
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_state_choice';
    }
}
