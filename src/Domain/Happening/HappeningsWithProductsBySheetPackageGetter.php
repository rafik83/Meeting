<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningsWithProductsBySheetPackageGetter
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return Happening[] indexed by Happening id
     */
    public function get(Sheet $sheet): array
    {
        $happeningsWithProducts = $this->happeningRepository->findWithProductsAndType($sheet->getEvent(), $sheet->getType());

        if (empty($happeningsWithProducts)) {
            return [];
        }

        $happeningsInPackage = [];
        $attributableOptions = $sheet->getPackage()->getAttributableOptions();

        foreach ($happeningsWithProducts as $happeningsWithProduct) {
            foreach ($happeningsWithProduct->getProducts() as $product) {
                if (isset($attributableOptions[$product->getId()])) {
                    $happeningsInPackage[$happeningsWithProduct->getId()] = $happeningsWithProduct;

                    break;
                }
            }
        }

        return $happeningsInPackage;
    }
}
