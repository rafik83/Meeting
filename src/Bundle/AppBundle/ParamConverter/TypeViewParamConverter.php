<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\ParamConverter;

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

        $type = $this->typeRepository->getTypeViewById($id, $locale);

        if (null === $type) {
            throw new NotFoundHttpException('Type not found');
        }

        $request->attributes->set($configuration->getName(), $type);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === TypeView::class;
    }
}
