<?php


namespace Proximum\Vimeet\Application\Command\Product;


use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

abstract class AbstractHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FileStorageInterface
     */
    protected $fileStorageInterface;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param FileStorageInterface $fileStorageInterface
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FileStorageInterface $fileStorageInterface = null
    ) {
        $this->productRepository = $productRepository;
        $this->fileStorageInterface = $fileStorageInterface;
    }
}
