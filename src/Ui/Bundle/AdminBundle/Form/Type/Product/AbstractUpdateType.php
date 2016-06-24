<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product;


use Proximum\Vimeet\Domain\Model\Product;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractUpdateType extends AbstractProductType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['product', 'locale']);
        $resolver->setAllowedTypes('product', Product::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update';
    }
}
