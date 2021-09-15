<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultFormExtension extends AbstractTypeExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => $this->requestStack->getMasterRequest()->getUri(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
