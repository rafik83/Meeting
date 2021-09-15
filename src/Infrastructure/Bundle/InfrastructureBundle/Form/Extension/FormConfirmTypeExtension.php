<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extension for the FormType, provides a confirm option
 */
class FormConfirmTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ButtonType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ButtonType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['confirm' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['confirm']) {
            $view->vars['confirm'] = $options['confirm'];
        }
    }
}
