<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Template\Form\ExportData;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserListView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserListViewNormalizer implements NormalizerInterface
{
    private const KEY_SHEET_ID = 'sheetId';
    private const KEY_SHEET_TITLE = 'sheetTitle';
    private const KEY_TYPE_TITLE = 'typeTitle';
    private const KEY_CATEGORY_TITLE = 'categoryTitle';
    private const KEY_USER_ID = 'userId';
    private const KEY_USER_FIRST_NAME = 'userFirstName';
    private const KEY_USER_LAST_NAME = 'userLastName';
    private const KEY_USER_EMAIL = 'userEmail';
    private const KEY_USER_PHONE = 'userPhone';
    private const KEY_USER_MOBILE_PHONE = 'userMobilePhone';
    private const KEY_SHEET_ADDRESS = 'sheetAddress';
    private const KEY_SHEET_ZIP_CODE = 'sheetZipCode';
    private const KEY_SHEET_CITY = 'sheetCity';
    private const KEY_SHEET_COUNTRY = 'sheetCountry';

    private const TRANSLATION_COLUMN_KEY = 'form_template_data_export.column.';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var UserListView $userListView */
        $userListView = $object;
        $locale = $userListView->locale;

        $firstRow = [
            self::KEY_SHEET_ID => $this->transCol(self::KEY_SHEET_ID, $locale),
            self::KEY_SHEET_TITLE => $this->transCol(self::KEY_SHEET_TITLE, $locale),
            self::KEY_TYPE_TITLE => $this->transCol(self::KEY_TYPE_TITLE, $locale),
            self::KEY_CATEGORY_TITLE => $this->transCol(self::KEY_CATEGORY_TITLE, $locale),
            self::KEY_USER_ID => $this->transCol(self::KEY_USER_ID, $locale),
            self::KEY_USER_FIRST_NAME => $this->transCol(self::KEY_USER_FIRST_NAME, $locale),
            self::KEY_USER_LAST_NAME => $this->transCol(self::KEY_USER_LAST_NAME, $locale),
            self::KEY_USER_EMAIL => $this->transCol(self::KEY_USER_EMAIL, $locale),
            self::KEY_USER_PHONE => $this->transCol(self::KEY_USER_PHONE, $locale),
            self::KEY_USER_MOBILE_PHONE => $this->transCol(self::KEY_USER_MOBILE_PHONE, $locale),
            self::KEY_SHEET_ADDRESS => $this->transCol(self::KEY_SHEET_ADDRESS, $locale),
            self::KEY_SHEET_ZIP_CODE => $this->transCol(self::KEY_SHEET_ZIP_CODE, $locale),
            self::KEY_SHEET_CITY => $this->transCol(self::KEY_SHEET_CITY, $locale),
            self::KEY_SHEET_COUNTRY => $this->transCol(self::KEY_SHEET_COUNTRY, $locale),
        ];

        foreach ($userListView->formTemplateObjectLabels as $objectKey => $objectLabel) {
            $firstRow[$objectKey] = $this->convertCharset($objectLabel);
        }

        $data[] = $firstRow;

        foreach ($userListView->userViews as $userDataView) {
            $row = [
                self::KEY_SHEET_ID => $userDataView->sheetId,
                self::KEY_SHEET_TITLE => $this->convertCharset($userDataView->sheetTitle),
                self::KEY_TYPE_TITLE => $this->convertCharset($userDataView->typeTitle),
                self::KEY_CATEGORY_TITLE => $this->convertCharset($userDataView->categoryTitle),
                self::KEY_USER_ID => $userDataView->userId,
                self::KEY_USER_FIRST_NAME => $this->convertCharset($userDataView->userFirstName),
                self::KEY_USER_LAST_NAME => $this->convertCharset($userDataView->userLastName),
                self::KEY_USER_EMAIL => $userDataView->userEmail,
                self::KEY_USER_PHONE => $this->formatPhoneNumber($userDataView->userPhone),
                self::KEY_USER_MOBILE_PHONE => $this->formatPhoneNumber($userDataView->userMobilePhone),
                self::KEY_SHEET_ADDRESS => $this->convertCharset($userDataView->sheetAddress),
                self::KEY_SHEET_ZIP_CODE => $this->convertCharset($userDataView->sheetZipCode),
                self::KEY_SHEET_CITY => $this->convertCharset($userDataView->sheetCity),
                self::KEY_SHEET_COUNTRY => $userDataView->sheetCountry,
            ];

            foreach ($userDataView->formTemplateDataByKey as $objectKey => $objectContent) {
                $row[$objectKey] = $this->convertCharset($objectContent);
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * @param string $phoneNumber
     *
     * @return null|string
     */
    private function formatPhoneNumber($phoneNumber): ?string
    {
        return !empty($phoneNumber) ? sprintf('\'%s\'', $phoneNumber) : null;
    }

    /**
     * @param string $input
     *
     * @return null|string
     */
    private function convertCharset($input): ?string
    {
        return Charset::convertString($input);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof UserListView && 'csv' === $format;
    }

    private function transCol(string $columnKey, string $locale): ?string
    {
        return $this->convertCharset(
            $this->translator->trans(
                sprintf('%s%s', self::TRANSLATION_COLUMN_KEY, $columnKey),
                [],
                'export',
                $locale
            )
        );
    }
}
