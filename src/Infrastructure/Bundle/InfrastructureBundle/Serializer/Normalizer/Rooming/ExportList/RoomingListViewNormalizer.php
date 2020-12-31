<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Rooming\ExportList;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Rooming\ExportList\RoomingListView;
use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RoomingListViewNormalizer implements NormalizerInterface
{
    private const KEY_SHEET_ID = 'sheetId';
    private const KEY_SHEET_TITLE = 'sheetTitle';
    private const KEY_SHEET_FOLLOWER = 'sheetFollower';
    private const KEY_SHEET_PLAN = 'roomPlan';
    private const KEY_TYPE_TITLE = 'typeTitle';
    private const KEY_USER_ID = 'userId';
    private const KEY_USER_FIRST_NAME = 'userFirstName';
    private const KEY_USER_LAST_NAME = 'userLastName';
    private const KEY_USER_MOBILE = 'mobile';
    private const KEY_USER_EMAIL = 'email';
    private const KEY_USER_GENDER = 'userGender';
    private const KEY_SPOT_REFERENCE = 'spotReference';
    private const KEY_ACCOMMODATION_TITLE = 'accommodationTitle';
    private const KEY_ROOM_TYPE = 'roomType';
    private const KEY_ROOM_NUMBER = 'roomNumber';
    private const KEY_ARRIVAL = 'arrival';
    private const KEY_DEPARTURE = 'departure';
    private const KEY_USER_COMMENT = 'userComment';
    private const KEY_USER_TASTING = 'userTasting';

    private const TRANSLATION_COLUMN_KEY = 'rooming_list_data_export.column.';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var RoomingListView $roomingListView */
        $roomingListView = $object;
        $locale = $roomingListView->locale;

        $data = [];
        $data[] = [
            self::KEY_SHEET_ID => $this->transCol(self::KEY_SHEET_ID, $locale),
            self::KEY_SHEET_TITLE => $this->transCol(self::KEY_SHEET_TITLE, $locale),
            self::KEY_SHEET_FOLLOWER => $this->transCol(self::KEY_SHEET_FOLLOWER, $locale),
            self::KEY_SHEET_PLAN => $this->transCol(self::KEY_SHEET_PLAN, $locale),
            self::KEY_TYPE_TITLE => $this->transCol(self::KEY_TYPE_TITLE, $locale),
            self::KEY_SPOT_REFERENCE => $this->transCol(self::KEY_SPOT_REFERENCE, $locale),
            self::KEY_USER_ID => $this->transCol(self::KEY_USER_ID, $locale),
            self::KEY_USER_GENDER => $this->transCol(self::KEY_USER_GENDER, $locale),
            self::KEY_USER_FIRST_NAME => $this->transCol(self::KEY_USER_FIRST_NAME, $locale),
            self::KEY_USER_LAST_NAME => $this->transCol(self::KEY_USER_LAST_NAME, $locale),
            self::KEY_USER_EMAIL => $this->transCol(self::KEY_USER_EMAIL, $locale),
            self::KEY_USER_MOBILE => $this->transCol(self::KEY_USER_MOBILE, $locale),
            self::KEY_USER_COMMENT => $this->transCol(self::KEY_USER_COMMENT, $locale),
            self::KEY_USER_TASTING => $this->transCol(self::KEY_USER_TASTING, $locale),

            self::KEY_ACCOMMODATION_TITLE => $this->transCol(self::KEY_ACCOMMODATION_TITLE, $locale),
            self::KEY_ROOM_TYPE => $this->transCol(self::KEY_ROOM_TYPE, $locale),
            self::KEY_ARRIVAL => $this->transCol(self::KEY_ARRIVAL, $locale),
            self::KEY_DEPARTURE => $this->transCol(self::KEY_DEPARTURE, $locale),
            self::KEY_ROOM_NUMBER => $this->transCol(self::KEY_ROOM_NUMBER, $locale),

            $this->addRoommateKey(self::KEY_SHEET_ID) => $this->transCol($this->addRoommateKey(self::KEY_SHEET_ID), $locale),
            $this->addRoommateKey(self::KEY_SHEET_TITLE) => $this->transCol($this->addRoommateKey(self::KEY_SHEET_TITLE), $locale),
            $this->addRoommateKey(self::KEY_SHEET_FOLLOWER) => $this->transCol($this->addRoommateKey(self::KEY_SHEET_FOLLOWER), $locale),
            $this->addRoommateKey(self::KEY_SHEET_PLAN) => $this->transCol($this->addRoommateKey(self::KEY_SHEET_PLAN), $locale),
            $this->addRoommateKey(self::KEY_TYPE_TITLE) => $this->transCol($this->addRoommateKey(self::KEY_TYPE_TITLE), $locale),
            $this->addRoommateKey(self::KEY_SPOT_REFERENCE) => $this->transCol($this->addRoommateKey(self::KEY_SPOT_REFERENCE), $locale),
            $this->addRoommateKey(self::KEY_USER_ID) => $this->transCol($this->addRoommateKey(self::KEY_USER_ID), $locale),
            $this->addRoommateKey(self::KEY_USER_GENDER) => $this->transCol($this->addRoommateKey(self::KEY_USER_GENDER), $locale),
            $this->addRoommateKey(self::KEY_USER_FIRST_NAME) => $this->transCol($this->addRoommateKey(self::KEY_USER_FIRST_NAME), $locale),
            $this->addRoommateKey(self::KEY_USER_LAST_NAME) => $this->transCol($this->addRoommateKey(self::KEY_USER_LAST_NAME), $locale),
            $this->addRoommateKey(self::KEY_USER_EMAIL) => $this->transCol($this->addRoommateKey(self::KEY_USER_EMAIL), $locale),
            $this->addRoommateKey(self::KEY_USER_MOBILE) => $this->transCol($this->addRoommateKey(self::KEY_USER_MOBILE), $locale),
            $this->addRoommateKey(self::KEY_USER_COMMENT) => $this->transCol($this->addRoommateKey(self::KEY_USER_COMMENT), $locale),
            $this->addRoommateKey(self::KEY_USER_TASTING) => $this->transCol($this->addRoommateKey(self::KEY_USER_TASTING), $locale),
        ];

        foreach ($roomingListView->stayViews as $stayView) {
            $userSheetViews = $stayView->userSheetViews;
            /** @var UserSheetView $user */
            $user = reset($userSheetViews);
            $roommate = end($userSheetViews);

            $stayNormalized = [
                self::KEY_SHEET_ID => $this->convertCharset($user->sheetIds),
                self::KEY_SHEET_TITLE => $this->convertCharset($user->sheetTitles),
                self::KEY_SHEET_FOLLOWER => $this->convertCharset($user->sheetFollowers),
                self::KEY_SHEET_PLAN => $this->convertCharset($user->sheetPlans),
                self::KEY_TYPE_TITLE => $this->convertCharset($user->typeTitles),
                self::KEY_SPOT_REFERENCE => $this->convertCharset($user->spotReferences),
                self::KEY_USER_ID => $this->convertCharset($user->userId),
                self::KEY_USER_GENDER => $this->transGender($user->gender, $locale),
                self::KEY_USER_FIRST_NAME => $this->convertCharset($user->firstName),
                self::KEY_USER_LAST_NAME => $this->convertCharset($user->lastName),
                self::KEY_USER_EMAIL => $this->convertCharset($user->email),
                self::KEY_USER_MOBILE => $this->convertCharset($user->mobile),
                self::KEY_USER_COMMENT => $this->convertCharset($user->comment),
                self::KEY_USER_TASTING => $this->convertCharset($user->tasting),

                self::KEY_ACCOMMODATION_TITLE => $this->convertCharset($stayView->accommodationTitle),
                self::KEY_ROOM_TYPE => $this->transRoomType($stayView->roomType, $locale),
                self::KEY_ARRIVAL => $stayView->arrivalDate,
                self::KEY_DEPARTURE => $stayView->departureDate,
                self::KEY_ROOM_NUMBER => $this->convertCharset($stayView->roomNumber),

                $this->addRoommateKey(self::KEY_SHEET_ID) => null,
                $this->addRoommateKey(self::KEY_SHEET_TITLE) => null,
                $this->addRoommateKey(self::KEY_SHEET_FOLLOWER) => null,
                $this->addRoommateKey(self::KEY_SHEET_PLAN) => null,
                $this->addRoommateKey(self::KEY_TYPE_TITLE) => null,
                $this->addRoommateKey(self::KEY_SPOT_REFERENCE) => null,
                $this->addRoommateKey(self::KEY_USER_ID) => null,
                $this->addRoommateKey(self::KEY_USER_GENDER) => null,
                $this->addRoommateKey(self::KEY_USER_FIRST_NAME) => null,
                $this->addRoommateKey(self::KEY_USER_LAST_NAME) => null,
                $this->addRoommateKey(self::KEY_USER_EMAIL) => null,
                $this->addRoommateKey(self::KEY_USER_MOBILE) => null,
                $this->addRoommateKey(self::KEY_USER_COMMENT) => null,
                $this->addRoommateKey(self::KEY_USER_TASTING) => null,
            ];

            if ($roommate instanceof UserSheetView && \count($stayView->userSheetViews) > 1) {
                $stayNormalized[$this->addRoommateKey(self::KEY_SHEET_ID)] = $this->convertCharset($roommate->sheetIds);
                $stayNormalized[$this->addRoommateKey(self::KEY_SHEET_TITLE)] = $this->convertCharset($roommate->sheetTitles);
                $stayNormalized[$this->addRoommateKey(self::KEY_SHEET_FOLLOWER)] = $this->convertCharset($roommate->sheetFollowers);
                $stayNormalized[$this->addRoommateKey(self::KEY_SHEET_PLAN)] = $this->convertCharset($roommate->sheetPlans);
                $stayNormalized[$this->addRoommateKey(self::KEY_TYPE_TITLE)] = $this->convertCharset($roommate->typeTitles);
                $stayNormalized[$this->addRoommateKey(self::KEY_SPOT_REFERENCE)] = $this->convertCharset($roommate->spotReferences);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_ID)] = $this->convertCharset($roommate->userId);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_GENDER)] = $this->transGender($roommate->gender, $locale);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_FIRST_NAME)] = $this->convertCharset($roommate->firstName);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_LAST_NAME)] = $this->convertCharset($roommate->lastName);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_EMAIL)] = $this->convertCharset($roommate->email);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_MOBILE)] = $this->convertCharset($roommate->mobile);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_COMMENT)] = $this->convertCharset($roommate->comment);
                $stayNormalized[$this->addRoommateKey(self::KEY_USER_TASTING)] = $this->convertCharset($roommate->tasting);
            }

            $data[] = $stayNormalized;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RoomingListView && 'csv' === $format;
    }

    private function convertCharset(?string $input): ?string
    {
        return Charset::convertString($input);
    }

    private function addRoommateKey(string $key): string
    {
        return 'roommate.' . $key;
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

    private function transGender(?string $gender, string $locale): ?string
    {
        return empty($gender)
            ? null
            : $this->convertCharset(
                $this->translator->trans(
                    sprintf('%s%s', 'gender.', $gender),
                    [],
                    'export',
                    $locale
                )
        );
    }

    private function transRoomType(string $roomType, string $locale): string
    {
        return $this->convertCharset(
            $this->translator->trans(
                sprintf('%sroomType.%s', self::TRANSLATION_COLUMN_KEY, $roomType),
                [],
                'export',
                $locale
            )
        );
    }
}
