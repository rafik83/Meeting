<?php


namespace Proximum\Vimeet\Application\Command\Product\Participant;


use Proximum\Vimeet\Application\Command\Product\AbstractHandler;

class UpdateParticipantHandler extends AbstractHandler
{
    /**
     * @param UpdateParticipant $updateParticipant
     */
    public function handle(UpdateParticipant $updateParticipant)
    {
        $product = $updateParticipant->product->updateParticipant(
            $updateParticipant->name,
            $updateParticipant->unitPrice,
            $updateParticipant->quantityMax
        );

        foreach ($updateParticipant->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $this->productRepository->update($product);
    }
}
