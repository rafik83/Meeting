<?php
/**
 * Created by PhpStorm.
 * User: taner
 * Date: 15/03/19
 * Time: 14:29
 */

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class CanRemoveMeeting
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

        return $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN') || $type->canRemoveMeeting();
    }
}
