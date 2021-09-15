<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Add a confirm on form if the confirm option with the message is given
 */
class ConfirmExtension extends AbstractTypeExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ConfirmExtension constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (false !== $options['confirm']) {
            $view->vars['attr']['data-confirm'] = $this->translator->trans($options['confirm'], [], 'forms');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
