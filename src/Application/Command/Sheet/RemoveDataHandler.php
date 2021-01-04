<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class RemoveDataHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var BuyableObjectResolver
     */
    private $buyableObjectResolver;

    /**
     * RemoveDataHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BuyableObjectResolver    $buyableObjectResolver
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BuyableObjectResolver $buyableObjectResolver
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->buyableObjectResolver = $buyableObjectResolver;
    }

    /**
     * Remove product from cart, data from sheet and update sheet
     *
     * @param RemoveData $command
     */
    public function handle(RemoveData $command)
    {
        $this->buyableObjectResolver->removePayableProduct($command->sheet, $command->templateObject);

        $command->templateObject->setData([]);

        $this->sheetRepository->set($command->sheet->setData($command->templateData->getData()));
    }
}
