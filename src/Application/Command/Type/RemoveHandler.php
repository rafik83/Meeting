<?php

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Exception\Type\TypeUsedBySheetException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RemoveHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param TypeRepositoryInterface  $typeRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->typeRepository  = $typeRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Remove $remove
     *
     * @throws TypeUsedBySheetException
     */
    public function handle(Remove $remove)
    {
        if ($this->sheetRepository->isThereAtLeastOneByType($remove->type)) {
            throw new TypeUsedBySheetException();
        }

        $this->typeRepository->remove($remove->type);
    }
}
