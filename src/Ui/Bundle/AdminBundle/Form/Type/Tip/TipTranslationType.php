<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TipTranslationType extends AbstractType
{
    /**
     * @var array
     */
    private $preferredLocales;

    /**
     * TipTranslationType constructor.
     *
     * @param array $preferredLocales
     */
    public function __construct(array $preferredLocales)
    {
        $this->preferredLocales = $preferredLocales;
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', LocaleType::class, [
                'preferred_choices' => $this->preferredLocales,
            ])
            ->add('title', TextType::class, [
                'required' => false,
            ])
            ->add('content', TextareaType::class);
    }

    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_translation';
    }
}
