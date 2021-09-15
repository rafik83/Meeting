<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver;

use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class AdminDomainValueResolver implements ArgumentValueResolverInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (AdminDomain::class !== $argument->getType()) {
            return false;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return false;
        }

        return $token->getUser() instanceof Admin;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield new AdminDomain($this->tokenStorage->getToken()->getUser());
    }
}
