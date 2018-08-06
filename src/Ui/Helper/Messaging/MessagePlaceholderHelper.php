<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Helper\Messaging;

use Proximum\Vimeet\Domain\Model\Messaging\Compose;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Helper service that provides menu items for generic tags & links to be used in WYSIWYG messaging message editor.
 */
class MessagePlaceholderHelper
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Placeholder data dispatched into three entries:
     *
     * - labels: menu group labels in WYSIWYG editor
     * - tags: tag placeholders and labels
     * - links: link placeholders and labels
     *
     * @return array
     */
    public function getPlaceholderData()
    {
        $placeholders = ['labels' => [], 'tags' => [], 'links' => []];

        foreach (self::getMenuLabels() as $key => $label) {
            $placeholders['labels'][$key] = $this->translator->trans($label);
        }

        foreach (self::getTagLabels() as $placeholder => $label) {
            $placeholders['tags'][] = [
                'value' => $placeholder,
                'text'  => $this->translator->trans($label),
            ];
        }

        foreach (self::getLinkLabels() as $placeholder => $label) {
            $placeholders['links'][] = [
                'value' => $placeholder,
                'text'  => $this->translator->trans($label),
            ];
        }

        return $placeholders;
    }

    /**
     * @return string[] With menu name as key, and menu label as value
     */
    private static function getMenuLabels()
    {
        return [
            'tags'  => 'admin.messaging.message.compose.tags.label',
            'links' => 'admin.messaging.message.compose.links.label',
        ];
    }

    /**
     * @return string[] With tag placeholder as key, and tag label as value
     */
    private static function getTagLabels()
    {
        return [
            Compose::TAG_EVENT_NAME              => 'admin.messaging.message.compose.tags.eventName',
            Compose::TAG_PARTICIPANT             => 'admin.messaging.message.compose.tags.participant',
            Compose::TAG_PARTICIPATION_TYPE      => 'admin.messaging.message.compose.tags.participationType',
            Compose::TAG_SHEET_PLANNING          => 'admin.messaging.message.compose.tags.sheetPlanning',
            Compose::TAG_CTA_AGENDA_CONFIRMATION => 'admin.messaging.message.compose.tags.cta.agendaConfirmation',
            Compose::TAG_CTA_EBADGE => 'admin.messaging.message.compose.tags.cta.ebadge',
        ];
    }

    /**
     * Label examples: 'Presentation sheet', 'Fiche de présentation', 'Package', 'Forfait', etc.
     *
     * @return string[] With link placeholder as key, and link label as value
     */
    private static function getLinkLabels()
    {
        return [
            Compose::LINK_SHEET                 => 'admin.messaging.message.compose.links.sheet',
            Compose::LINK_PACKAGE               => 'admin.messaging.message.compose.links.package',
            Compose::LINK_ORDERS                => 'admin.messaging.message.compose.links.orders',
            Compose::LINK_AGENDA                => 'admin.messaging.message.compose.links.agenda',
            Compose::LINK_PROGRAM               => 'admin.messaging.message.compose.links.program',
            Compose::LINK_CATALOG               => 'admin.messaging.message.compose.links.catalog',
            Compose::LINK_MEETING_REQUEST       => 'admin.messaging.message.compose.links.meetingRequest',
            Compose::LINK_ACTIVACTE_ACCOUNT     => 'admin.messaging.message.compose.links.activateAccount',
            Compose::LINK_EXPORT_MEETING_SHEET  => 'admin.messaging.message.compose.links.exportMeetingSheet',
            Compose::LINK_VALIDATE_MOBILE_PHONE => 'admin.messaging.message.compose.links.validateMobilePhone',
        ];
    }
}
