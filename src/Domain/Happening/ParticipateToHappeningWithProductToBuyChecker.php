<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ParticipateToHappeningWithProductToBuyChecker
{
    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var PackageProductsNeededByHappening */
    private $packageProductsNeededByHappening;

    public function __construct(
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        PackageProductsNeededByHappening $packageProductsNeededByHappening
    ) {
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->packageProductsNeededByHappening = $packageProductsNeededByHappening;
    }

    public function canParticipate(Participant $participant, Happening $toHappening): bool
    {
        if (!$toHappening->hasProducts()) {
            return true;
        }

        if (!$participant->getSheet()->getPackage()->isPassable()) {
            return true;
        }

        $productNeededByHappening = $this->packageProductsNeededByHappening->get(
            $participant->getSheet()->getPackage(),
            $toHappening
        );

        $noProductInPackageCanBeBoughtToParticipateToHappening = empty($productNeededByHappening);

        if ($noProductInPackageCanBeBoughtToParticipateToHappening) {
            return true;
        }

        return $this->productAttributedToParticipantRepository->participantHasAtLeastOneProduct(
            $participant,
            $productNeededByHappening
        );
    }
}
