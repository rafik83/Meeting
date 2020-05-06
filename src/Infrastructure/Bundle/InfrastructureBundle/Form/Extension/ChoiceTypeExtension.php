<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Extension for the ChoiceType, provides:
 * - Block prefixes for the choice items
 */
class ChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view as $child) {
            $this->addBlockPrefix($view, 'choice_item');
            $this->addBlockPrefix($view, $form->getName() . '_item');
        }
    }
}
