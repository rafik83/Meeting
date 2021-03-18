<?php

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class GetAllowedTypesByAdminQueryHandler
{
    /** @var TypeRepositoryInterface $typeRepository */
    private $typeRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(TypeRepositoryInterface $typeRepository, \DateTimeInterface $datetime)
    {
        $this->typeRepository = $typeRepository;
        $this->datetime = $datetime;
    }

    public function handle(GetAllowedTypesByAdminQuery $query): iterable
    {
        return $this->typeRepository->getAllowedTypesExcludedCurrentEventByAdmin(
            $query->admin,
            $query->event,
            $this->datetime
        );
    }
}
