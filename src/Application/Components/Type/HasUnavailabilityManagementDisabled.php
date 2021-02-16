<?php

namespace Proximum\Vimeet\Application\Components\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class HasUnavailabilityManagementDisabled
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter)
    {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        $type = $sheet->getType();

        // 1rst rule
        $isEnabled = Type::TYPE_MANAGEMENT_UNAVAILABLE === $type->getAvailabilityType();
        // 2nd rule
        $isEnabled = $isEnabled
            || (Type::TYPE_MANAGEMENT_NONE === $type->getAvailabilityType()
                && $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN'));

        return !$isEnabled;
    }
}
