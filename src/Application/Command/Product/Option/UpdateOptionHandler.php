<?php

namespace Proximum\Vimeet\Application\Command\Product\Option;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;

class UpdateOptionHandler extends AbstractHandler
{
    /**
     * @param UpdateOption $updateOption
     */
    public function handle(UpdateOption $updateOption): void
    {
        $canUpdatePriceAndVat = $this->updatePriceResolver->resolve($updateOption->product);
        $product = $updateOption->product->updateOption(
            $updateOption->name,
            $this->fileStorageInterface->upload($updateOption->file),
            $updateOption->quantityMax,
            $updateOption->availabilityCurrent,
            $updateOption->availabilityMax,
            $updateOption->updatable,
            $canUpdatePriceAndVat ? $updateOption->unitPrice : $updateOption->product->getUnitPrice(),
            $canUpdatePriceAndVat ? $updateOption->vat : $updateOption->product->getVat(),
            $updateOption->deletableUntil,
            $updateOption->subjectedToValidation,
            $updateOption->buyableUntil,
            $updateOption->attributable,
            $updateOption->canScanParticipant
        );

        $product->setHappenings($updateOption->happenings);

        foreach ($updateOption->translations as $locale => $translation) {
            $product->translate(
                $locale,
                $translation['title'],
                null,
                $translation['description'],
                $translation['addon'],
                $translation['subjectedToValidationHelp']
            );
        }

        $this->productRepository->update($product);
    }
}
