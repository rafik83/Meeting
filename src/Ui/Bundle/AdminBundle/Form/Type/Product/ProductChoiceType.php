<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Helper\CurrencyFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChoiceType extends AbstractType
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CurrencyFormatter
     */
    private $currencyFormatter;

    /**
     * ProductChoiceType constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param CurrencyFormatter          $currencyFormatter
     */
    public function __construct(ProductRepositoryInterface $productRepository, CurrencyFormatter $currencyFormatter)
    {
        $this->productRepository = $productRepository;
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'choice_label'     => function (Options $options) {
                return function (Product $product) use ($options) {
                    if (true === $options['display_price']) {
                        $price = $this->currencyFormatter->format($product->getUnitPrice(), $product->getCurrency(), $options['locale']);

                        return sprintf('%s (%s)', $product->getName(), $price);
                    }

                    return $product->getName();
                };
            },
            'repositoryMethod' => function (Options $options) {
                return function (ProductRepositoryInterface $productRepository) use ($options) {
                    return $productRepository->findByEvent($options['event']);
                };
            },
            'choices'          => function (Options $options) {
                return $options['repositoryMethod']($this->productRepository);
            },
            'display_price'    => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_choice';
    }
}
