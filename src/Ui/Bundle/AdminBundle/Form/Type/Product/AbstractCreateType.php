<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractCreateType extends AbstractProductType
{
    /** @var bool */
    private $vatEnabled;

    /**
     * @param bool $vatEnabled
     */
    public function __construct(bool $vatEnabled)
    {
        $this->vatEnabled = $vatEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('unitPrice', NumberType::class, [
                'attr' => [
                    'min' => 0,
                ],
            ])
        ;
        if (true === $this->vatEnabled) {
            $builder
                ->add('vat', NumberType::class, [
                    'attr' => [
                        'min' => 0,
                    ],
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_create';
    }
}
