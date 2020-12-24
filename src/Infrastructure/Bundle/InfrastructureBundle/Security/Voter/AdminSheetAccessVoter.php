<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\AdminSheetAccess;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminSheetAccessVoter extends Voter
{
    /**
     * @var AdminSheetAccess
     */
    private $adminSheetAccess;

    /**
     * AdminSheetAccessVoter constructor.
     *
     * @param AdminSheetAccess $adminSheetAccess
     */
    public function __construct(AdminSheetAccess $adminSheetAccess)
    {
        $this->adminSheetAccess = $adminSheetAccess;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return 'PERMISSION_SHEET_ACCESS' === $attribute && $subject instanceof Sheet;
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

        return $this->adminSheetAccess->canAccess($user, $subject);
    }
}
