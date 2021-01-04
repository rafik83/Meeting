<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductIncludedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductChoiceType::class, [
                'required'         => true,
                'locale'           => $options['locale'],
                'event'            => $options['event'],
                'placeholder'      => 'form.product_create_plan.children.productIncluded.prototype.children.product.placeholder',
                'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                    $types = [Product::TYPE_OPTION, Product::TYPE_PARTICIPANT, Product::TYPE_PLANNING];

                    return $productRepository->findByEventAndTypes($options['event'], $types);
                },
                'attr'             => [
                    'data-shared-choices-collection-item' => 'products',
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'required' => true,
                'attr'     => ['min' => 0],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
    }
}
