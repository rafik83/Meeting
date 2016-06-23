<?php


namespace Proximum\Vimeet\Application\Command\Product\Option;


use Proximum\Vimeet\Domain\Model\Product;

class UpdateOption extends AbstractOption
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;

        $this->name = $product->getName();
        $this->translations = $product->getTranslations();
        $this->unitPrice = $product->getUnitPrice();
        $this->quantityMax = $product->getQuantityMax();
        $this->availabilityCurrent = $product->getAvailabilityCurrent();
        $this->availabilityMax = $product->getAvailabilityMax();
        $this->updatable = $product->isUpdatable();
        $this->updatableUntil = $product->getUpdatableUntil();

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

