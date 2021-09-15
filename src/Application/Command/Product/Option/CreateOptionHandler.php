<?php

namespace Proximum\Vimeet\Application\Command\Product\Option;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;
use Proximum\Vimeet\Domain\Model\Product;

class CreateOptionHandler extends AbstractHandler
{
    /**
     * @param CreateOption $createOption
     */
    public function handle(CreateOption $createOption): void
    {
        $product = Product::createOption(
            $createOption->event,
            $createOption->name,
            $this->fileStorageInterface->upload($createOption->file),
            $createOption->unitPrice,
            $createOption->vat,
            $createOption->quantityMax,
            $createOption->availabilityCurrent,
            $createOption->availabilityMax,
            $createOption->updatable,
            $createOption->deletableUntil,
            $createOption->subjectedToValidation,
            $createOption->buyableUntil,
            $createOption->attributable,
            $createOption->canScanParticipant
        );

        $product->setHappenings($createOption->happenings);

        foreach ($createOption->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'],
                $translation['addon'], $translation['subjectedToValidationHelp']);
        }

        $this->productRepository->add($product);
    }
}
