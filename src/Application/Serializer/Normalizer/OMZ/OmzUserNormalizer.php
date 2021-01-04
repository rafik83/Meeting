<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\OMZ;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Serializer\Normalizer\AbstractNormalizer;
use Proximum\Vimeet\Application\View\OMZ\OmzUserListView;
use Proximum\Vimeet\Application\View\OMZ\OmzUserView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OmzUserNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_USER_ID             = 'participantId';
    const COL_TITLE               = 'title'; // Gender
    const COL_FIRSTNAME           = 'firstName';
    const COL_LASTNAME            = 'lastName';
    const COL_COMPANY             = 'companyName';
    const COL_PARTICIPATION_TYPE  = 'type';
    const COL_DESCRIPTION         = 'description';
    const COL_POSITION            = 'position';
    const COL_PHONE_PREFIX        = 'phonePrefix';
    const COL_PHONE_NUMBER        = 'phoneNumber';
    const COL_EMAIL               = 'email';
    const COL_MOBILE_PHONE_PREFIX = 'mobilePhonePrefix';
    const COL_MOBILE_PHONE        = 'mobilePhone';
    const COL_SCHEDULE            = 'planning';

    /**
     * OmzUserNormalizer constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof OmzUserListView && 'csv' === $format;
    }

    /**
     * {@inheritdoc}
     *
     * @param OmzUserListView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedUserRawData = [];
        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($object->userViews as $userView) {
            $normalizedUserRawData[] = $this->normalizeUserRawData(
                $this->getOmzUserViewRawData($userView),
                $charset
            );
        }

        return $normalizedUserRawData;
    }

    /**
     * Returns an array of normalized data from a participant's schedule raw data
     * (normalizing includes charset encoding, string substitution for boolean values, etc.)
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeUserRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach ($rawData as $fieldKey => $input) {
            $normalizedData[$fieldKey] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        return $normalizedData;
    }

    /**
     * @param OmzUserView $userView
     *
     * @return array
     */
    private function getOmzUserViewRawData(OmzUserView $userView)
    {
        return [
            self::COL_USER_ID             => $userView->participantId,
            self::COL_COMPANY             => $userView->companyName,
            self::COL_DESCRIPTION         => $userView->description,
            self::COL_PARTICIPATION_TYPE  => $userView->participationType,
            self::COL_TITLE               => $userView->gender,
            self::COL_FIRSTNAME           => $userView->firstname,
            self::COL_LASTNAME            => $userView->lastname,
            self::COL_POSITION            => $userView->position,
            self::COL_PHONE_PREFIX        => $userView->phonePrefix,
            self::COL_PHONE_NUMBER        => $userView->phoneNumber,
            self::COL_EMAIL               => $userView->email,
            self::COL_MOBILE_PHONE_PREFIX => $userView->mobilePhonePrefix,
            self::COL_MOBILE_PHONE        => $userView->mobilePhoneNumber,
            self::COL_SCHEDULE            => $userView->planning,
        ];
    }
}
