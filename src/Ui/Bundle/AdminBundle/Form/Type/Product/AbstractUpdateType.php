<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractUpdateType extends AbstractProductType
{
    /** @var UpdatePriceResolver */
    private $updatePriceResolver;

    /** @var bool */
    private $vatEnabled;

    /**
     * @param UpdatePriceResolver $updatePriceResolver
     * @param bool                $vatEnabled
     */
    public function __construct(UpdatePriceResolver $updatePriceResolver, bool $vatEnabled)
    {
        $this->updatePriceResolver = $updatePriceResolver;
        $this->vatEnabled = $vatEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (true === $this->updatePriceResolver->resolve($options['product'])) {
            $builder->add('unitPrice', NumberType::class, [
                'attr' => [
                    'min' => 0,
                ],
            ]);

            if (true === $this->vatEnabled) {
                $builder->add('vat', NumberType::class, [
                    'attr' => [
                        'min' => 0,
                    ],
                ]);
            }
        }
    }

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
