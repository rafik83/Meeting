<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Mail\AbstractCustomizedMail;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Template;

use function Sentry\captureEvent;

class MailerAdapter implements MailerInterface
{
    /** @var \Swift_Mailer */
    private $mailer;

    private Environment $twig;

    /** @var TranslatorAdapter */
    private $translator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        \Swift_Mailer $mailer,
        Environment $twig,
        TranslatorAdapter $translator,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->logger = $logger;
        // make sure scheme is https
        $router->initScheme();
    }

    /**
     * Send Mail via Swift Mailer
     *
     * @param AbstractMail $mail
     */
    public function send(AbstractMail $mail)
    {
        /** @var Template $template */
        $template = $this->twig->loadTemplate($mail->getTemplate());
        $body = $template->render(['mail' => $mail]);

        if ($mail->hasToTranslateSubject()) {
            $subject = $this->translator->trans(
                $mail->getSubject(),
                $mail->getSubjectParameters(),
                'mail',
                $mail->getLocale()
            );
        } else {
            $subject = $mail->getSubject();
        }

        $message = new \Swift_Message($subject);
        $message->setFrom($mail->getSender());
        $receivers = array_unique($mail->getReceivers());

        foreach ($receivers as $receiver) {
            $message->addTo($receiver);
        }

        if (true === $mail->sendToEmailTeam()
            && ($mail instanceof UserMail || $mail instanceof AbstractCustomizedMail)
        ) {
            $mailTeam = $mail->getEvent()->getEmailTeam();

            if (!in_array($mailTeam, $receivers, true)) {
                $message->setBcc($mailTeam);
            }
        }

        $message
            ->setBody($body)
            ->setContentType('text/html');

        $message->getHeaders()->addTextHeader('X-Message-ID', $mail->getMessageId());

        $failedReceivers = [];
        $this->mailer->send($message, $failedReceivers);

        $this->handleResults(
            $receivers,
            $failedReceivers,
            $subject,
            $mail->getMessageId()
        );
        $this->mailer->getTransport()->stop();
    }

    protected function handleResults(array $receivers, array $failedReceivers, string $subject, ?string $messageId = null)
    {
        $context = ['subject' => $subject, 'messageId' => $messageId];

        foreach ($receivers as $receiver) {
            if (in_array($receiver, $failedReceivers, true)) {
                $errorMessage = sprintf('Failed to send email %s to %s', $messageId, $receiver);
                $this->logger->error($errorMessage, $context + ['to' => $receiver]);
                captureEvent(
                    [
                        'message' => $errorMessage,
                        'level' => 'error',
                        'extra' => ['to' => $receiver] + $context
                    ]
                );

                continue;
            }

            $this->logger->info(
                sprintf('Email sent to %s', $receiver),
                $context + ['to' => $receiver]
            );
        }
    }

    protected function getMailer()
    {
        return $this->mailer;
    }
}
