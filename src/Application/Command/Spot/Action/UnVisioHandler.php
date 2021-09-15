<?php

namespace Proximum\Vimeet\Application\Command\Spot\Action;

use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UnVisioHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param UnVisio $unVisio
     */
    public function handle(UnVisio $unVisio)
    {
        $this->spotRepository->set($unVisio->spot->unVisio());
    }
}
