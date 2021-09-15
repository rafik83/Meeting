<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\AdminSheetAccess;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User\Event\Authorization;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminUserEventAccessVoter extends Voter
{
    /** @var AdminSheetAccess */
    private $adminSheetAccess;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(AdminSheetAccess $adminSheetAccess, SheetRepositoryInterface $sheetRepository)
    {
        $this->adminSheetAccess = $adminSheetAccess;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return 'PERMISSION_USER_ACCESS' === $attribute && $subject instanceof Authorization;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $admin = $token->getUser();

        if (!$admin instanceof Admin) {
            return false;
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($subject->getUser(), $subject->getEvent());

        if (empty($sheets)) {
            return false;
        }

        // check that current user has access to all sheets of subject user
        foreach ($sheets as $sheet) {
            if (!$this->adminSheetAccess->canAccess($admin, $sheet)) {
                return false;
            }
        }

        return true;
    }
}
