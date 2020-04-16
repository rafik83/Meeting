<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order\Export;

class SharedColumnsTranslationView
{
    public const COLUMN_ORDER_ID                = 'order_id';
    public const COLUMN_ORDER_DATE              = 'order_date';
    public const COLUMN_SHEET_ID                = 'sheet_id';
    public const COLUMN_SHEET_TITLE             = 'sheet_title';
    public const COLUMN_INVOICE_NUMBER          = 'invoice_number';
    public const COLUMN_INVOICE_DATE            = 'invoice_date';
    public const COLUMN_BILLING_INFO_GENDER     = 'billing_info_gender';
    public const COLUMN_BILLING_INFO_LAST_NAME  = 'billing_info_last_name';
    public const COLUMN_BILLING_INFO_FIRST_NAME = 'billing_info_first_name';
    public const COLUMN_BILLING_INFO_POSITION   = 'billing_info_position';
    public const COLUMN_BILLING_INFO_PHONE      = 'billing_info_phone';
    public const COLUMN_BILLING_INFO_MOBILE     = 'billing_info_mobile';
    public const COLUMN_BILLING_INFO_EMAIL      = 'billing_info_email';
    public const COLUMN_BILLING_INFO_COMPANY    = 'billing_info_company';
    public const COLUMN_BILLING_INFO_STREET     = 'billing_info_street';
    public const COLUMN_BILLING_INFO_ZIP_CODE   = 'billing_info_zip_code';
    public const COLUMN_BILLING_INFO_CITY       = 'billing_info_city';
    public const COLUMN_BILLING_INFO_COUNTRY    = 'billing_info_country';
    public const COLUMN_BILLING_INFO_VAT_NUMBER = 'billing_info_vat_number';
    public const COLUMN_BILLING_INFO_REFERENCE  = 'billing_info_reference';
    public const COLUMN_ORDER_TOTAL_WITHOUT_VAT = 'order_total_without_vat';
    public const COLUMN_ORDER_TOTAL_VAT         = 'order_total_vat';
    public const COLUMN_ORDER_TOTAL_WITH_VAT    = 'order_total_with_vat';

    /** @var string */
    public $orderId;

    /** @var string */
    public $orderDate;

    /** @var string */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var string */
    public $gender;

    /** @var string */
    public $lastName;

    /** @var string */
    public $firstName;

    /** @var string */
    public $position;

    /** @var string */
    public $phone;

    /** @var string */
    public $mobile;

    /** @var string */
    public $email;

    /** @var string */
    public $company;

    /** @var string */
    public $street;

    /** @var string */
    public $zipCode;

    /** @var string */
    public $city;

    /** @var string */
    public $country;

    /** @var string */
    public $vatNumber;

    /** @var string */
    public $reference;

    /** @var string */
    public $invoiceNumber;

    /** @var string */
    public $invoiceDate;

    /** @var string */
    public $totalWithoutVat;

    /** @var string */
    public $totalVat;

    /** @var string */
    public $totalWithVat;

    /**
     * @param string $orderId
     * @param string $orderDate
     * @param string $sheetId
     * @param string $sheetTitle
     * @param string $invoiceNumber
     * @param string $invoiceDate
     * @param string $gender
     * @param string $lastName
     * @param string $firstName
     * @param string $position
     * @param string $phone
     * @param string $mobile
     * @param string $email
     * @param string $company
     * @param string $street
     * @param string $zipCode
     * @param string $city
     * @param string $country
     * @param string $vatNumber
     * @param string $reference
     * @param string $totalWithoutVat
     * @param string $totalVat
     * @param string $totalWithVat
     */
    public function __construct(
        $orderId,
        $orderDate,
        $sheetId,
        $sheetTitle,
        $invoiceNumber,
        $invoiceDate,
        $gender,
        $lastName,
        $firstName,
        $position,
        $phone,
        $mobile,
        $email,
        $company,
        $street,
        $zipCode,
        $city,
        $country,
        $vatNumber,
        $reference,
        $totalWithoutVat,
        $totalVat,
        $totalWithVat
    ) {
        $this->orderId    = $orderId;
        $this->orderDate  = $orderDate;
        $this->sheetId    = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate   = $invoiceDate;
        $this->gender     = $gender;
        $this->lastName   = $lastName;
        $this->firstName  = $firstName;
        $this->position   = $position;
        $this->phone      = $phone;
        $this->mobile     = $mobile;
        $this->email      = $email;
        $this->company    = $company;
        $this->street     = $street;
        $this->zipCode    = $zipCode;
        $this->city       = $city;
        $this->country    = $country;
        $this->vatNumber  = $vatNumber;
        $this->reference  = $reference;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->totalVat = $totalVat;
        $this->totalWithVat = $totalWithVat;
    }

    /**
     * @return array
     */
    public function getAllColumns(): array
    {
        return [
            self::COLUMN_ORDER_ID                => $this->orderId,
            self::COLUMN_ORDER_DATE              => $this->orderDate,
            self::COLUMN_SHEET_ID                => $this->sheetId,
            self::COLUMN_SHEET_TITLE             => $this->sheetTitle,
            self::COLUMN_INVOICE_NUMBER          => $this->invoiceNumber,
            self::COLUMN_INVOICE_DATE            => $this->invoiceDate,
            self::COLUMN_BILLING_INFO_GENDER     => $this->gender,
            self::COLUMN_BILLING_INFO_LAST_NAME  => $this->lastName,
            self::COLUMN_BILLING_INFO_FIRST_NAME => $this->firstName,
            self::COLUMN_BILLING_INFO_POSITION   => $this->position,
            self::COLUMN_BILLING_INFO_PHONE      => $this->phone,
            self::COLUMN_BILLING_INFO_MOBILE     => $this->mobile,
            self::COLUMN_BILLING_INFO_EMAIL      => $this->email,
            self::COLUMN_BILLING_INFO_COMPANY    => $this->company,
            self::COLUMN_BILLING_INFO_STREET     => $this->street,
            self::COLUMN_BILLING_INFO_ZIP_CODE   => $this->zipCode,
            self::COLUMN_BILLING_INFO_CITY       => $this->city,
            self::COLUMN_BILLING_INFO_COUNTRY    => $this->country,
            self::COLUMN_BILLING_INFO_VAT_NUMBER => $this->vatNumber,
            self::COLUMN_BILLING_INFO_REFERENCE  => $this->reference,
            self::COLUMN_ORDER_TOTAL_WITHOUT_VAT => $this->totalWithoutVat,
            self::COLUMN_ORDER_TOTAL_VAT         => $this->totalVat,
            self::COLUMN_ORDER_TOTAL_WITH_VAT    => $this->totalWithVat,
        ];
    }
}
