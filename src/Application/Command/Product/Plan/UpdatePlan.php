<?php


namespace Application\Command\Product;


use Proximum\Vimeet\Domain\Model\Product;

class UpdatePlan extends AbstractPlan
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;

        foreach ($product->getEvent()->getLocales() as $locale)
        {
            $this->translations[$locale] = [
                'title'                     => $product->getTitle($locale),
                'description'               => $product->getDescription($locale),
                'addon'                     => $product->getAddon($locale),
                'subjectedToValidationHelp' => $product->getSubjectedToValidationHelp($locale),
            ];
        }
    }
}