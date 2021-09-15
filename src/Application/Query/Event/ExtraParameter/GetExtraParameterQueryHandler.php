<?php

namespace Proximum\Vimeet\Application\Query\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class GetExtraParameterQueryHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(GetExtraParameterQuery $query): ?ExtraParameter
    {
        return $this->extraParameterRepository->findByEventAndType($query->event, $query->type);
    }
}
