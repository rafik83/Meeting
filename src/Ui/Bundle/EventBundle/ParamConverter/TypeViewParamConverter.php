<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TypeViewParamConverter implements ParamConverterInterface
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id     = $request->attributes->get('typeView');
        $locale = $request->getLocale();

        /** @var EventDomain $eventDomain */
        $eventDomain = $request->attributes->get('eventDomain');
        $typeView    = $this->typeRepository->getTypeViewByIdAndEvent($id, $eventDomain->getEvent(), $locale);

        if (null === $typeView) {
            throw new NotFoundHttpException('Type not found');
        }

        $request->attributes->set($configuration->getName(), $typeView);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return TypeView::class === $configuration->getClass();
    }
}
