<?php


namespace Proximum\Vimeet\Application\Command\Product\Option;


use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class UpdateOptionHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorageInterface;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param FileStorageInterface $fileStorageInterface
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FileStorageInterface $fileStorageInterface
    ) {
        $this->productRepository = $productRepository;
        $this->fileStorageInterface = $fileStorageInterface;
    }

    /**
     * @param UpdateOption $createOption
     */
    public function handle(UpdateOption $createOption)
    {
        
    }
}