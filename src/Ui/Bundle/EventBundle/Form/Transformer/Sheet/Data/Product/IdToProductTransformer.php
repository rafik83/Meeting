<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product;

use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

class IdToProductTransformer implements DataTransformerInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * IdToProductTransformer constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Transform a product id into a product
     *
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (empty($value)) {
            return null;
        }

        return $this->productRepository->findById((int) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
