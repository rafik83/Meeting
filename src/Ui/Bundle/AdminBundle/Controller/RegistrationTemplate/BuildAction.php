<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BuildAction
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationChecker, EngineInterface $engine)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
    }

    public function __invoke(Request $request, RegistrationTemplate $registrationTemplate, string $locale): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationChecker->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
        ) {
            throw new AccessDeniedException();
        }

        return $this->engine->renderResponse('AdminBundle:RegistrationTemplate:builder.html.twig', [
            'registrationTemplate' => $registrationTemplate,
            'event' => $registrationTemplate->getEvent(),
        ]);
    }
}
