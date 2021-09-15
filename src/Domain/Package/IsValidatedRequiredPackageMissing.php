<?php

namespace Proximum\Vimeet\Domain\Package;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class IsValidatedRequiredPackageMissing
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        if (!$sheet->getType()->isPackageRequired()) {
            return false;
        }

        return empty($this->orderRepository->findNotCancelledBySheet($sheet));
    }
}
