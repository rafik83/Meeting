<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Transactional\Mail;

use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event\PreRegisteredMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order\OrderConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ThirdParty\Comexposium\SSO\Participant\ParticipantAddedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\HappeningParticipationAutomaticallyUpdatedMail;

final class Constant
{
    /*
     * Registration
     */
    public const TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED = 'mail_pre_registered';

    /*
     * User
     */
    public const TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT = 'mail_user_activate_account';
    public const TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL = 'mail_user_change_new_mail';
    public const TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL = 'mail_user_change_old_mail';
    public const TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE = 'mail_user_complete_profile';
    public const TRANSACTIONAL_MAIL_KEY_USER_REGISTERED = 'mail_user_registered';
    public const TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD = 'mail_user_reset_password';
    public const TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED = 'mail_user_changed_password_confirmed';

    /*
     * Sheet
     */
    public const TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED = 'mail_sheet_type_changed';
    public const TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED = 'mail_sheet_group_created';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED = 'mail_sheet_refused';
    public const TRANSACTIONAL_MAIL_SHEET_REFUSED_SUBJECT = 'mail.sheet.refused.subject';
    public const TRANSACTIONAL_MAIL_SHEET_REFUSED_TEMPLATE = 'MailBundle:Mail:Sheet/sheetRefused.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATED = 'mail_sheet_validated';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATED_SUBJECT = 'mail.sheet.validated.subject';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE = 'MailBundle:Mail:Sheet/sheetValidated.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_VALIDATE = 'mail_sheet_validation_validate';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_SUBJECT = 'mail.sheet.validation.validate.subject';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE = 'MailBundle:Mail:Sheet/sheetValidationValidate.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_DRAFT = 'mail_sheet_validation_draft';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_SUBJECT = 'mail.sheet.validation.draft.subject';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE = 'MailBundle:Mail:Sheet/sheetValidationDraft.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED = 'mail_sheet_invoiced';
    public const TRANSACTIONAL_MAIL_SHEET_INVOICED_SUBJECT = 'mail.sheet.invoiced.subject';
    public const TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE = 'MailBundle:Mail:Invoice/sheetInvoiced.html.twig';

    /*
     * Participant
     */
    public const TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION = 'mail_participation_added_confirmation';

    /*
     * Third party
     */
    public const TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION = 'mail_third_party_comexposium_participation_added_confirmation';

    /*
     * Happenings
     */
    public const TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED = 'mail_happening_participation_automatically_updated';

    /*
     * Orders
     */
    public const TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED = 'mail_order_confirmed';

    /*
     * Transactions
     */
    public const TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED = 'mail_transaction_confirmed';

    public const TRANSACTIONAL_MAIL_LIST = [
        self::TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED => [
            'subject' => PreRegisteredMail::SUBJECT,
            'availableParameters' => [
                '%urlEventAccountParticipant%',
            ],
            'isCustomizableByType' => true,
            'template' => PreRegisteredMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT => [
            'subject' => User\ActivateAccountMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateAccount%',
            ],
            'isCustomizableByType' => false,
            'template' => User\ActivateAccountMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL => [
            'subject' => User\ChangeNewMailAddressMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateNewMail%',
            ],
            'isCustomizableByType' => false,
            'template' => User\ChangeNewMailAddressMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL => [
            'subject' => User\ChangeOldMailAddressMail::SUBJECT,
            'availableParameters' => [
                '%newMail%',
            ],
            'isCustomizableByType' => false,
            'template' => User\ChangeOldMailAddressMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE => [
            'subject' => User\CompleteProfileMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateAccountAlreadyKnown%',
            ],
            'isCustomizableByType' => true,
            'template' => User\CompleteProfileMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED => [
            'subject' => User\RegisterAccountMail::SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => false,
            'template' => User\RegisterAccountMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD => [
            'subject' => User\ResetPasswordMail::SUBJECT,
            'availableParameters' => [
                '%urlEventCreateNewPassword%',
            ],
            'isCustomizableByType' => false,
            'template' => User\ResetPasswordMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED => [
            'subject' => User\ResetPasswordConfirmMail::SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => false,
            'template' => User\ResetPasswordConfirmMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED => [
            'subject' => Sheet\SheetChangeTypeMail::SUBJECT,
            'availableParameters' => [
                '%fromType%',
                '%toType%',
                '%urlEventSheetLocale%',
            ],
            'isCustomizableByType' => true,
            'template' => Sheet\SheetChangeTypeMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_REFUSED_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_REFUSED_TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATED => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATED_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_VALIDATE => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_DRAFT => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_INVOICED_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED => [
            'subject' => Sheet\SheetGroupCreatedMail::SUBJECT,
            'availableParameters' => [
                '%urlEventSheetGroupIndex%',
            ],
            'isCustomizableByType' => false,
            'template' => Sheet\SheetGroupCreatedMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION => [
            'subject' => Sheet\AddParticipantMail::SUBJECT,
            'availableParameters' => [
                '%guestEmail%',
            ],
            'isCustomizableByType' => true,
            'template' => Sheet\AddParticipantMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION => [
            'subject' => ParticipantAddedMail::SUBJECT,
            'availableParameters' => [
                '%urlEvent%',
            ],
            'isCustomizableByType' => true,
            'template' => ParticipantAddedMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => [
            'subject' => HappeningParticipationAutomaticallyUpdatedMail::SUBJECT,
            'availableParameters' => [
                '%happeningParticipationChanges%'
            ],
            'isCustomizableByType' => true,
            'template' => HappeningParticipationAutomaticallyUpdatedMail::TEMPLATE
        ],
        self::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED => [
            'subject' => OrderConfirmMail::SUBJECT,
            'availableParameters' => [
                '%orderDate%',
                '%orderNumber%',
                '%urlEventProForma%',
            ],
            'isCustomizableByType' => true,
            'template' => OrderConfirmMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED => [
            'subject' => TransactionConfirmMail::SUBJECT,
            'availableParameters' => [
                '%transactionTotal%',
            ],
            'isCustomizableByType' => true,
            'template' => TransactionConfirmMail::TEMPLATE,
        ],
    ];

    public const TRANSACTIONAL_MAIL_GENERIC_PARAMETERS = [
        '%event%',
        '%firstName%',
        '%lastName%',
        '%participationType%',
    ];

    public const TRANSACTIONAL_MAIL_GENERIC_CUSTOMIZABLE_BY_TYPE_PARAMETERS = [
        '%participationType%',
    ];
}
