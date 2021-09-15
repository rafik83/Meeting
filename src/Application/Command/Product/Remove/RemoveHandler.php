<?php

namespace Proximum\Vimeet\Application\Command\Product\Remove;

use Proximum\Vimeet\Application\Exception\Product\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Product\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class RemoveHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RemoveAuthorizationChecker */
    private $removeAuthorizationChecker;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RemoveAuthorizationChecker $removeAuthorizationChecker
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RemoveAuthorizationChecker $removeAuthorizationChecker
    ) {
        $this->productRepository = $productRepository;
        $this->removeAuthorizationChecker = $removeAuthorizationChecker;
    }

    /**
     * @param Remove $command
     *
     * @throws CanNotBeRemovedException
     */
    public function handle(Remove $command)
    {
        if (!$this->removeAuthorizationChecker->canBeRemoved($command->product)) {
            throw new CanNotBeRemovedException(
                sprintf('The product %s can not be removed', $command->product->getId())
            );
        }

        $this->productRepository->remove($command->product);
    }
}
