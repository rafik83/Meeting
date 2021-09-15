<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SheetParamConverter implements ParamConverterInterface
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $sheetId = $request->attributes->get('sheet');

        if (null === $sheetId) {
            return false;
        }

        /** @var null|EventDomain $eventDomain */
        $eventDomain = $request->attributes->get('eventDomain');
        $sheet       = $this->sheetRepository->getSheetById($sheetId);

        if (null === $sheet) {
            throw new NotFoundHttpException('Sheet not found');
        }

        if (null !== $eventDomain && $eventDomain->getEvent() !== $sheet->getEvent()) {
            throw new NotFoundHttpException('Sheet not found in that event');
        }

        $request->attributes->set($configuration->getName(), $sheet);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return Sheet::class === $configuration->getClass();
    }
}
