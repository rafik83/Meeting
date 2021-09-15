<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\Summary;

use Proximum\Vimeet\Application\Command\Package\PromotionCode\Add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionCodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('promotionCode', TextType::class, [
                'required'    => false,
                'placeholder' => 'form.package_summary_promotion_code.placeholder',
                'label'       => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'data_class' => Add::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'package_summary_promotion_code';
    }
}
