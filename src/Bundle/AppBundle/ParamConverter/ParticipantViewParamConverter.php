<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\ParamConverter;

use Proximum\Vimeet\Domain\Model\ParticipantView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParticipantViewParamConverter implements ParamConverterInterface
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id     = $request->attributes->get('participantView');
        $locale = $request->getLocale();
        $type   = $this->participantRepository->getParticipantView($id, $locale);

        if (null === $type) {
            throw new NotFoundHttpException('Participant not found');
        }

        $request->attributes->set($configuration->getName(), $type);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === ParticipantView::class;
    }
}
