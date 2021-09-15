<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\AdminEventAccess;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminTemplateAccessVoter extends Voter
{
    const PERMISSION_TEMPLATE_EDIT = 'PERMISSION_TEMPLATE_EDIT';

    /**
     * @var AdminEventAccess
     */
    private $adminEventAccess;

    /**
     * @param AdminEventAccess $adminEventAccess
     */
    public function __construct(AdminEventAccess $adminEventAccess)
    {
        $this->adminEventAccess = $adminEventAccess;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return self::PERMISSION_TEMPLATE_EDIT === $attribute && $subject instanceof AbstractTemplate;
    }

    /**
     * @param string           $attribute
     * @param AbstractTemplate $subject
     * @param TokenInterface   $token
     *
     * @return bool
     */
    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Admin) {
            return false;
        }

        if ($subject->hasEvent()) {
            return $this->adminEventAccess->canAccess($user, $subject->getEvent());
        }

        return Admin::ROLE_SUPER_ADMIN === $user->getRole();
    }
}
