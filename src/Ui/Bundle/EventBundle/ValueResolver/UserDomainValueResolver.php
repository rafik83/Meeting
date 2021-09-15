<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\UserDomainProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class UserDomainValueResolver implements ArgumentValueResolverInterface
{
    private UserDomainProvider $userDomainProvider;

    public function __construct(UserDomainProvider $userDomainProvider)
    {
        $this->userDomainProvider = $userDomainProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (UserDomain::class !== $argument->getType()) {
            return false;
        }

        return $this->userDomainProvider->isUserDomainConnected();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->userDomainProvider->createUserDomain();
    }
}
