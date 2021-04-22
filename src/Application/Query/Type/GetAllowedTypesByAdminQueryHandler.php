<?php

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class GetAllowedTypesByAdminQueryHandler
{
    /** @var TypeRepositoryInterface $typeRepository */
    private $typeRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(TypeRepositoryInterface $typeRepository, \DateTimeInterface $dateTime)
    {
        $this->typeRepository = $typeRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(GetAllowedTypesByAdminQuery $query): iterable
    {
        return $this->typeRepository->getAllowedTypesExcludedCurrentEventByAdmin(
            $query->admin,
            $query->event,
            $this->dateTime
        );
    }
}
