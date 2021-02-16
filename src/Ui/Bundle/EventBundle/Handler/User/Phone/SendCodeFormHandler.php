<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Phone\SendCodeType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;

class SendCodeFormHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var FormFactory */
    private $formFactory;

    /** @var CommandBus */
    private $commandBus;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param FormFactory                       $formFactory
     * @param CommandBus                        $commandBus
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        FormFactory $formFactory,
        CommandBus $commandBus
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->formFactory              = $formFactory;
        $this->commandBus               = $commandBus;
    }

    /**
     * @param SendCodeForm $sendCodeForm
     *
     * @return SendCodeView
     */
    public function handle(SendCodeForm $sendCodeForm)
    {
        $request = $sendCodeForm->request;
        $locale  = $request->getLocale();
        $user    = $sendCodeForm->user;
        $event   = $sendCodeForm->event;

        if (false === $sendCodeForm->ignorePhoneAlreadyValidated) {
            $userEventPhoneValidated = $this->userEventPhoneRepository->findValidated($user, $event);

            if (null !== $userEventPhoneValidated) {
                return new SendCodeView(SendCodeView::SEND_CODE_FORM_NOT_SHOWN);
            }
        }

        $tipTranslationViews = $this->commandBus->handle(
            new TipTranslationViewByUserQuery(
                $event,
                $user,
                TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                $locale
            )
        );

        if (empty($tipTranslationViews)) {
            return new SendCodeView(SendCodeView::SEND_CODE_FORM_NOT_SHOWN);
        }

        $sendCode = new SendCode($user, $event, $sendCodeForm->mobileNumberToValidate ?? $user->getMobile(), $locale);

        $formOptions = [
            'country' => $event->getCountry(),
            'submit'  => true,
        ];

        if (!empty($sendCodeForm->actionRoute)) {
            $formOptions['action'] = $sendCodeForm->actionRoute;
        }

        $form = $this->formFactory->create(SendCodeType::class, $sendCode, $formOptions);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($sendCode);

                return new SendCodeView(SendCodeView::SEND_CODE_SUCCESS);
            } catch (InvalidReceiverException $invalidReceiverException) {
                $form->get('phone')->addError(new FormError('validators.send_code.error.invalidReceiver'));
            }
        }

        return new SendCodeView(
            SendCodeView::SEND_CODE_SHOW_FORM,
            $form,
            $tipTranslationViews
        );
    }
}
