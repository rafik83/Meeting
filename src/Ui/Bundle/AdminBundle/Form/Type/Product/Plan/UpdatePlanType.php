<?php


namespace Ui\Bundle\AdminBundle\Form\Type\Product\Plan;


use Application\Command\Product\UpdatePlan;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Plan\AbstractPlanType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdatePlanType extends AbstractPlanType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdatePlan::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update_plan';
    }
}