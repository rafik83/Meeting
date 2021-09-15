<?php

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class HasSheet
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * HasSheet constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function on(Event $event)
    {
        return $this->sheetRepository->countByEvent($event) > 0;
    }
}
