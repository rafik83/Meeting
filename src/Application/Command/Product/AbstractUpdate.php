<?php


namespace Proximum\Vimeet\Application\Command\Product;


use Proximum\Vimeet\Domain\Model\Product;

abstract class AbstractUpdate extends AbstractProduct
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;

        $this->name = $product->getName();
        $this->unitPrice = $product->getUnitPrice();
        $this->quantityMax = $product->getQuantityMax();

        foreach ($product->getEvent()->getLocales() as $locale)
        {
            $this->translations[$locale] = [
                'title'                     => $product->getTitle($locale),
                'heading'                   => $product->getHeading($locale),
                'description'               => $product->getDescription($locale),
                'addon'                     => $product->getAddon($locale),
                'subjectedToValidationHelp' => $product->getSubjectedToValidationHelp($locale),
            ];
        }
    }
}
