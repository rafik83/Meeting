<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter;

use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Exception\Event\EventException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventDomainParamConverter implements ParamConverterInterface
{
    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /**
     * @param EventByHostResolver $eventByHostResolver
     */
    public function __construct(EventByHostResolver $eventByHostResolver)
    {
        $this->eventByHostResolver = $eventByHostResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundHttpException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            $event = $this->eventByHostResolver->resolveEventFromHostAndLocale($request->getHost(), $request->getLocale());
        } catch (EventException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        $request->attributes->set($configuration->getName(), new EventDomain($event));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return EventDomain::class === $configuration->getClass();
    }
}
