<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\AdminSheetAccess;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\UserEvent;
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
        return 'PERMISSION_USER_ACCESS' === $attribute && $subject instanceof UserEvent;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Admin) {
            return false;
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($subject->getUser(), $subject->getEvent());

        if (empty($sheets)) {
            return false;
        }

        // check that current user has access to all sheets of subject user
        foreach ($sheets as $sheet) {
            if (!$this->adminSheetAccess->canAccess($user, $sheet)) {
                return false;
            }
        }

        return true;
    }
}
