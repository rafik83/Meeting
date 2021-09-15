<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class RemoveHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /**
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     */
    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @param Remove $remove
     */
    public function handle(Remove $remove)
    {
        $this->extraParameterRepository->remove($remove->extraParameter);
    }
}
