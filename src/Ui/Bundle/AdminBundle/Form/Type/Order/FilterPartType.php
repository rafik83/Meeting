<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterPartType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductChoiceType::class, [
                'label'        => 'form.order.filter_by_bought_product',
                'placeholder'  => '',
                'select2'      => true,
                'event'        => $options['event'],
                'locale'       => $options['locale'],
                'group_by'     => function (Product $product) {
                    return sprintf('form.product_choice.group_by.type.%s', $product->getType());
                },
                'choice_value' => function ($choice) {
                    if ($choice instanceof Product) {
                        return $choice->getId();
                    }

                    return $choice;
                },
            ])
            ->add('enabled', HiddenType::class)
            ->add('cancelled', CancelledChoiceType::class)
        ;
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
        return 'order_part_filter';
    }
}
