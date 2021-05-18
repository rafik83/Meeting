<?php

namespace Proximum\Vimeet\Domain\Transactional\Mail;

use Proximum\Vimeet\Domain\Model\Messaging\Compose;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Agenda\VersionDiffChangedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event\PreRegisteredMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\HappeningParticipationAutomaticallyUpdatedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order\OrderConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ThirdParty\Comexposium\SSO\Participant\ParticipantAddedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

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
    public const TRANSACTIONAL_MAIL_SHEET_REFUSED_TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/sheetRefused_full_text.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATED = 'mail_sheet_validated';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATED_SUBJECT = 'mail.sheet.validated.subject';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE = 'MailBundle:Mail:Sheet/sheetValidated.html.twig';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/sheetValidated_full_text.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_VALIDATE = 'mail_sheet_validation_validate';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_SUBJECT = 'mail.sheet.validation.validate.subject';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE = 'MailBundle:Mail:Sheet/sheetValidationValidate.html.twig';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/sheetValidationValidate_full_text.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_DRAFT = 'mail_sheet_validation_draft';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_SUBJECT = 'mail.sheet.validation.draft.subject';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE = 'MailBundle:Mail:Sheet/sheetValidationDraft.html.twig';
    public const TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/sheetValidationDraft_full_text.html.twig';

    public const TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED = 'mail_sheet_invoiced';
    public const TRANSACTIONAL_MAIL_SHEET_INVOICED_SUBJECT = 'mail.sheet.invoiced.subject';
    public const TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE = 'MailBundle:Mail:Invoice/sheetInvoiced.html.twig';
    public const TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Invoice/sheetInvoiced_full_text.html.twig';

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

    /*
     * Agenda
     */
    public const TRANSACTIONAL_MAIL_KEY_AGENDA_VERSION_DIFF_CHANGED = 'mail_agenda_version_diff_changed';

    /*
     * Meeting
     */
    public const TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP = 'mail_meeting_follow_up';

    public const TRANSACTIONAL_MAIL_LIST = [
        self::TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED => [
            'subject' => PreRegisteredMail::SUBJECT,
            'availableParameters' => [
                '%urlEventAccountParticipant%',
                '%urlEventAccountParticipantWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => PreRegisteredMail::TEMPLATE,
            'template_full_text' => PreRegisteredMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT => [
            'subject' => User\ActivateAccountMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateAccount%',
                '%urlEventActivateAccountWithCTA%',
            ],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => User\ActivateAccountMail::TEMPLATE,
            'template_full_text' => User\ActivateAccountMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL => [
            'subject' => User\ChangeNewMailAddressMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateNewMail%',
                '%urlEventActivateNewMailWithCTA%',
                '%currentEmail%',
            ],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => User\ChangeNewMailAddressMail::TEMPLATE,
            'template_full_text' => User\ChangeNewMailAddressMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL => [
            'subject' => User\ChangeOldMailAddressMail::SUBJECT,
            'availableParameters' => [
                '%newMail%',
            ],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => User\ChangeOldMailAddressMail::TEMPLATE,
            'template_full_text' => User\ChangeOldMailAddressMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE => [
            'subject' => User\CompleteProfileMail::SUBJECT,
            'availableParameters' => [
                '%urlEventActivateAccountAlreadyKnown%',
                '%urlEventActivateAccountAlreadyKnownWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => User\CompleteProfileMail::TEMPLATE,
            'template_full_text' => User\CompleteProfileMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED => [
            'subject' => User\RegisterAccountMail::SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => User\RegisterAccountMail::TEMPLATE,
            'template_full_text' => User\RegisterAccountMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD => [
            'subject' => User\ResetPasswordMail::SUBJECT,
            'availableParameters' => [
                '%urlEventCreateNewPassword%',
                '%urlEventCreateNewPasswordWithCTA%',
            ],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => User\ResetPasswordMail::TEMPLATE,
            'template_full_text' => User\ResetPasswordMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED => [
            'subject' => User\ResetPasswordConfirmMail::SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => User\ResetPasswordConfirmMail::TEMPLATE,
            'template_full_text' => User\ResetPasswordConfirmMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED => [
            'subject' => Sheet\SheetChangeTypeMail::SUBJECT,
            'availableParameters' => [
                '%fromType%',
                '%toType%',
                '%urlEventSheet%',
                '%urlEventSheetWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => Sheet\SheetChangeTypeMail::TEMPLATE,
            'template_full_text' => Sheet\SheetChangeTypeMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_REFUSED_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_REFUSED_TEMPLATE,
            'template_full_text' => self::TRANSACTIONAL_MAIL_SHEET_REFUSED_TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATED => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATED_SUBJECT,
            'availableParameters' => [],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE,
            'template_full_text' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_VALIDATE => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_SUBJECT,
            'availableParameters' => [
                '%urlSheet%',
                '%urlSheetWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE,
            'template_full_text' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_DRAFT => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_SUBJECT,
            'availableParameters' => [
                '%urlEventSheet%',
                '%urlEventSheetWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE,
            'template_full_text' => self::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED => [
            'subject' => self::TRANSACTIONAL_MAIL_SHEET_INVOICED_SUBJECT,
            'availableParameters' => [
                '%invoiceLinks%',
                '%ordersLink%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => self::TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE,
            'template_full_text' => self::TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED => [
            'subject' => Sheet\SheetGroupCreatedMail::SUBJECT,
            'availableParameters' => [
                '%urlEventSheetGroupIndex%',
                '%urlEventSheetGroupIndexWithCTA%',
            ],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => Sheet\SheetGroupCreatedMail::TEMPLATE,
            'template_full_text' => Sheet\SheetGroupCreatedMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION => [
            'subject' => Sheet\AddParticipantMail::SUBJECT,
            'availableParameters' => [
                '%guestEmail%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => Sheet\AddParticipantMail::TEMPLATE,
            'template_full_text' => Sheet\AddParticipantMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION => [
            'subject' => ParticipantAddedMail::SUBJECT,
            'availableParameters' => [
                '%urlEvent%',
                '%urlEventWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => true,
            'template' => ParticipantAddedMail::TEMPLATE,
            'template_full_text' => ParticipantAddedMail::TEMPLATE_FULL_TEXT,
        ],
        // TODO: add missing prepare handler
        // self::TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => [
        //     'subject' => HappeningParticipationAutomaticallyUpdatedMail::SUBJECT,
        //     'availableParameters' => [
        //         '%happeningParticipationChanges%'
        //     ],
        //     'isCustomizableByType' => true,
        //     'isHidden' => false,
        //     'template' => HappeningParticipationAutomaticallyUpdatedMail::TEMPLATE,
        //     'template_full_text' => HappeningParticipationAutomaticallyUpdatedMail::TEMPLATE_FULL_TEXT,
        // ],
        self::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED => [
            'subject' => OrderConfirmMail::SUBJECT,
            'availableParameters' => [
                '%orderDate%',
                '%orderNumero%',
                '%urlEventProForma%',
                '%urlEventProFormaWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => OrderConfirmMail::TEMPLATE,
            'template_full_text' => OrderConfirmMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED => [
            'subject' => TransactionConfirmMail::SUBJECT,
            'availableParameters' => [
                '%transactionTotal%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => TransactionConfirmMail::TEMPLATE,
            'template_full_text' => TransactionConfirmMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_AGENDA_VERSION_DIFF_CHANGED => [
            'subject' => VersionDiffChangedMail::SUBJECT,
            'availableParameters' => [
                '%agendaModifications%',
                '%agendaLink%',
                '%agendaLinkWithCTA%',
            ],
            'isCustomizableByType' => true,
            'isHidden' => false,
            'template' => VersionDiffChangedMail::TEMPLATE,
            'template_full_text' => VersionDiffChangedMail::TEMPLATE_FULL_TEXT,
        ],
        self::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP => [
            'subject' => Meeting\MeetingFollowUpMail::SUBJECT,
            'availableParameters' => [
                '%evaluatedSheet%',
                '%evaluatingSheet%',
                '%meetingEvaluation%',
                '%metParticipants%',
            ],
            'isCustomizableByType' => false,
            'isHidden' => false,
            'template' => Meeting\MeetingFollowUpMail::TEMPLATE,
            'template_full_text' => Meeting\MeetingFollowUpMail::TEMPLATE_FULL_TEXT,
        ],
    ];

    public const TRANSACTIONAL_MAIL_GENERIC_PARAMETERS = [
        '%event%',
        '%firstName%',
        '%lastName%',
    ];

    public const TRANSACTIONAL_MAIL_LEGACY_GENERIC_PARAMETERS = [
        '%firstname%',
        '%lastname%',
        '%participant%',
    ];

    public const TRANSACTIONAL_MAIL_GENERIC_CUSTOMIZABLE_BY_TYPE_PARAMETERS = [
        '%participationType%',
    ];

    public static function getEditorPlaceholders(): array
    {
        return Compose::getAllPlaceholders();
    }
}
