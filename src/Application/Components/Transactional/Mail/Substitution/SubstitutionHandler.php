<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Exception\Messaging\UndefinedSubstitutionProviderException;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class SubstitutionHandler
{
    /** @var SubstitutionsProviders */
    private $substitutionsProviders;

    public function __construct(SubstitutionsProviders $substitutionsProviders)
    {
        $this->substitutionsProviders = $substitutionsProviders;
    }

    public function handle(AbstractPrepareMail $prepareMail, Message $message): SubstitutionResult
    {
        $locale = $prepareMail->event->getAvailableLocale($prepareMail->locale);
        $subject = $message->getSubject($locale);
        $content = $message->getContent($locale);

        $availableParameters = array_merge(
            Constant::TRANSACTIONAL_MAIL_GENERIC_PARAMETERS,
            Constant::TRANSACTIONAL_MAIL_LEGACY_GENERIC_PARAMETERS,
            Constant::TRANSACTIONAL_MAIL_LIST[$prepareMail->type]['availableParameters'],
            Constant::TRANSACTIONAL_MAIL_LIST[$prepareMail->type]['isCustomizableByType']
                ? Constant::TRANSACTIONAL_MAIL_GENERIC_CUSTOMIZABLE_BY_TYPE_PARAMETERS
                : []
        );

        foreach ($availableParameters as $availableParameter) {
            $foundInSubject = strpos($subject, $availableParameter);
            $foundInContent = strpos($content, $availableParameter);

            if (false !== $foundInSubject || false !== $foundInContent) {
                try {
                    $substitute = $this->substitutionsProviders
                        ->getSubstitution($availableParameter)
                        ->substitute($prepareMail);

                    if (false !== $foundInSubject) {
                        $subject = str_replace(
                            $availableParameter,
                            $substitute,
                            $subject
                        );
                    }

                    if (false !== $foundInContent) {
                        $content = str_replace(
                            $availableParameter,
                            $substitute,
                            $content
                        );
                    }
                } catch (UndefinedSubstitutionProviderException $exception) {
                    continue;
                }
            }
        }

        return new SubstitutionResult($subject, $content);
    }
}
