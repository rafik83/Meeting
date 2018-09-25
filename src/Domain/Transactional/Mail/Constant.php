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
            'template' => PreRegisteredMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT => [
            'subject' => User\ActivateAccountMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateAccount%',
            ],
            'template' => User\ActivateAccountMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL => [
            'subject' => User\ChangeNewMailAddressMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateNewMail%',
            ],
            'template' => User\ChangeNewMailAddressMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL => [
            'subject' => User\ChangeOldMailAddressMail::SUBJECT,
            'availableParameters' => [
                '%newMail%',
            ],
            'template' => User\ChangeOldMailAddressMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE => [
            'subject' => User\CompleteProfileMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateAccountAlreadyKnown%',
            ],
            'template' => User\CompleteProfileMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED => [
            'subject' => User\RegisterAccountMail::SUBJECT,
            'availableParameters' => [],
            'template' => User\RegisterAccountMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD => [
            'subject' => User\ResetPasswordMail::SUBJECT,
            'availableParameters' => [
                '%urlEventCreateNewPassword%',
            ],
            'template' => User\ResetPasswordMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED => [
            'subject' => User\ResetPasswordConfirmMail::SUBJECT,
            'availableParameters' => [],
            'template' => User\ResetPasswordConfirmMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED => [
            'subject' => Sheet\SheetChangeTypeMail::SUBJECT,
            'availableParameters' => [
                '%fromType%',
                '%toType%',
                '%urlEventSheetLocale%',
            ],
            'template' => Sheet\SheetChangeTypeMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED => [
            'subject' => Sheet\SheetGroupCreatedMail::SUBJECT,
            'availableParameters' => [
                '%urlEventSheetGroupIndex%',
            ],
            'template' => Sheet\SheetGroupCreatedMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION => [
            'subject' => Sheet\AddParticipantMail::SUBJECT,
            'availableParameters' => [
                '%guestEmail%',
            ],
            'template' => Sheet\AddParticipantMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION => [
            'subject' => ParticipantAddedMail::SUBJECT,
            'availableParameters' => [
                '%urlEvent%',
            ],
            'template' => ParticipantAddedMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => [
            'subject' => HappeningParticipationAutomaticallyUpdatedMail::SUBJECT,
            'availableParameters' => [
                '%happeningParticipationChanges%'
            ],
            'template' => HappeningParticipationAutomaticallyUpdatedMail::TEMPLATE
        ],
        self::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED => [
            'subject' => OrderConfirmMail::SUBJECT,
            'availableParameters' => [
                '%orderDate%',
                '%orderNumber%',
                '%urlEventProForma%',
            ],
            'template' => OrderConfirmMail::TEMPLATE,
        ],
        self::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED => [
            'subject' => TransactionConfirmMail::SUBJECT,
            'availableParameters' => [
                '%transactionTotal%',
            ],
            'template' => TransactionConfirmMail::TEMPLATE,
        ],
    ];

    public const TRANSACTIONAL_MAIL_GENERIC_PARAMETERS = [
        '%event%',
        '%firstName%',
        '%lastName%',
        '%participationType%'
    ];
}
