<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Exception\Messaging\UndefinedSubstitutionProviderException;
use Proximum\Vimeet\Domain\Model\MessageInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class SubstitutionHandler
{
    /** @var SubstitutionsProviders */
    private $substitutionsProviders;

    public function __construct(SubstitutionsProviders $substitutionsProviders)
    {
        $this->substitutionsProviders = $substitutionsProviders;
    }

    public function handle(AbstractPrepareMail $prepareMail, MessageInterface $message): SubstitutionResult
    {
        $locale = $prepareMail->event->getAvailableLocale($prepareMail->locale);
        $subject = $message->getSubject($locale);
        $content = $message->getContent($locale);

        $sheetParameters = array_merge(
            Constant::getEditorPlaceholders(),
            Constant::TRANSACTIONAL_MAIL_GENERIC_CUSTOMIZABLE_BY_TYPE_PARAMETERS
        );

        if (!$prepareMail->type) {
            $availableParameters = array_merge(
                Constant::TRANSACTIONAL_MAIL_GENERIC_PARAMETERS,
                Constant::TRANSACTIONAL_MAIL_LEGACY_GENERIC_PARAMETERS,
                $sheetParameters
            );
        } else {
            $availableParameters = array_merge(
                Constant::TRANSACTIONAL_MAIL_GENERIC_PARAMETERS,
                Constant::TRANSACTIONAL_MAIL_LEGACY_GENERIC_PARAMETERS,
                Constant::TRANSACTIONAL_MAIL_LIST[$prepareMail->type]['availableParameters'],
                Constant::TRANSACTIONAL_MAIL_LIST[$prepareMail->type]['isCustomizableByType']
                    ? $sheetParameters
                    : []
            );
        }

        $subjectSubstitutions = [];
        $contentSubstitutions = [];

        foreach ($availableParameters as $placeholder) {
            $foundInSubject = strpos($subject, $placeholder);
            $foundInContent = strpos($content, $placeholder);

            if (false !== $foundInSubject || false !== $foundInContent) {
                try {
                    $substitute = $this->substitutionsProviders
                        ->getSubstitution($placeholder)
                        ->substitute($prepareMail);

                    if (false !== $foundInSubject) {
                        $subjectSubstitutions[$placeholder] = $substitute;
                    }

                    if (false !== $foundInContent) {
                        $contentSubstitutions[$placeholder] = $substitute;
                    }
                } catch (UndefinedSubstitutionProviderException $exception) {
                    continue;
                }
            }
        }

        foreach ($subjectSubstitutions as $placeholder => $subjectSubstitution) {
            $subject = str_replace(
                $placeholder,
                $subjectSubstitution,
                $subject
            );
        }

        foreach ($contentSubstitutions as $placeholder => $contentSubstitution) {
            $content = str_replace(
                $placeholder,
                $contentSubstitution,
                $content
            );
        }

        return new SubstitutionResult(
            $subject,
            $content,
            $subjectSubstitutions,
            $contentSubstitutions
        );
    }
}
