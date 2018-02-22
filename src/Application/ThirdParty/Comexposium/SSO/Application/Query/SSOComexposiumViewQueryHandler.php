<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\View\SSOComexposiumView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class SSOComexposiumViewQueryHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var null|string */
    private $comexposiumSSOLoaderLibEndpoint;

    /**
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param null|string                       $comexposiumSSOLoaderLibEndpoint
     */
    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ?string $comexposiumSSOLoaderLibEndpoint
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumSSOLoaderLibEndpoint = $comexposiumSSOLoaderLibEndpoint;
    }

    public function handle(SSOComexposiumViewQuery $query): ?SSOComexposiumView
    {
        $ssoEnabled = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED);

        if ($ssoEnabled === null) {
            return null;
        }

        $salon = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_SALON);
        $sessionSalon = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON);
        $application = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_APPLICATION);

        if ($salon === null || $sessionSalon === null || $application === null) {
            return null;
        }

        return new SSOComexposiumView(
            $salon->getValue(),
            $sessionSalon->getValue(),
            $application->getValue(),
            $query->locale === 'fr' ? 'fre-FR' : 'eng-GB',
            $query->email,
            $this->comexposiumSSOLoaderLibEndpoint
        );
    }
}
