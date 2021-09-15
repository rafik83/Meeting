<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Event;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UpdateInvoicePrefixValidator extends ConstraintValidator
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * UpdateInvoicePrefixValidator constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            $this->context
                ->buildViolation('validators.event.update.invoicePrefix.disabled.label')
                ->atPath('invoicePrefix')
                ->addViolation();
        }
    }
}
