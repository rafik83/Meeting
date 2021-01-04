<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Validator\Constraint;

class FormTemplateBlockStepDataValidator extends ParticipantDataValidator
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Block) {
            $this->context->buildViolation('Block expected')->addViolation();
        }

        $objects = $value->getEditableObjects();

        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $objects = $value->getObjectsEditableByAdmin();
        }

        $validator = $this->context->getValidator()->inContext($this->context);

        foreach ($objects as $key => $object) {
            $class      = $this->objectsConstraint[$object->getType()];
            $constraint = new $class(['key' => $key]);
            $validator->validate($object, $constraint, ['form_template_block_step', 'Default']);
        }
    }
}
