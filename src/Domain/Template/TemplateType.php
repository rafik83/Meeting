<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

class TemplateType
{
    const TEMPLATE_OBJECT_TYPE_BUTTON_LINK   = 'button-link';
    const TEMPLATE_OBJECT_TYPE_COLLECTION    = 'collection';
    const TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT = 'editable-text';
    const TEMPLATE_OBJECT_TYPE_IMAGE         = 'image';
    const TEMPLATE_OBJECT_TYPE_MEDIA         = 'media';
    const TEMPLATE_OBJECT_TYPE_NOMENCLATURE  = 'nomenclature';
    const TEMPLATE_OBJECT_TYPE_PARTICIPANT   = 'participant';
    const TEMPLATE_OBJECT_TYPE_TAG           = 'tag';
    const TEMPLATE_OBJECT_TYPE_TEXT          = 'text';
    const TEMPLATE_OBJECT_TYPE_TELEPHONE     = 'telephone';
    const TEMPLATE_OBJECT_TYPE_COUNTRY       = 'country';
    const TEMPLATE_OBJECT_TYPE_URL           = 'url';
    const TEMPLATE_OBJECT_TYPE_TAGS          = 'tags';
    const TEMPLATE_OBJECT_TYPE_GENDER        = 'gender';
    const TEMPLATE_OBJECT_TYPE_BOOLEAN       = 'boolean';

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TEMPLATE_OBJECT_TYPE_BUTTON_LINK   => self::TEMPLATE_OBJECT_TYPE_BUTTON_LINK,
            self::TEMPLATE_OBJECT_TYPE_COLLECTION    => self::TEMPLATE_OBJECT_TYPE_COLLECTION,
            self::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT => self::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT,
            self::TEMPLATE_OBJECT_TYPE_IMAGE         => self::TEMPLATE_OBJECT_TYPE_IMAGE,
            self::TEMPLATE_OBJECT_TYPE_MEDIA         => self::TEMPLATE_OBJECT_TYPE_MEDIA,
            self::TEMPLATE_OBJECT_TYPE_NOMENCLATURE  => self::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
            self::TEMPLATE_OBJECT_TYPE_PARTICIPANT   => self::TEMPLATE_OBJECT_TYPE_PARTICIPANT,
            self::TEMPLATE_OBJECT_TYPE_TAG           => self::TEMPLATE_OBJECT_TYPE_TAG,
            self::TEMPLATE_OBJECT_TYPE_TEXT          => self::TEMPLATE_OBJECT_TYPE_TEXT,
            self::TEMPLATE_OBJECT_TYPE_TELEPHONE     => self::TEMPLATE_OBJECT_TYPE_TELEPHONE,
            self::TEMPLATE_OBJECT_TYPE_COUNTRY       => self::TEMPLATE_OBJECT_TYPE_COUNTRY,
            self::TEMPLATE_OBJECT_TYPE_URL           => self::TEMPLATE_OBJECT_TYPE_URL,
            self::TEMPLATE_OBJECT_TYPE_TAGS          => self::TEMPLATE_OBJECT_TYPE_TAGS,
            self::TEMPLATE_OBJECT_TYPE_GENDER        => self::TEMPLATE_OBJECT_TYPE_GENDER,
            self::TEMPLATE_OBJECT_TYPE_BOOLEAN       => self::TEMPLATE_OBJECT_TYPE_BOOLEAN,
        ];
    }
}
