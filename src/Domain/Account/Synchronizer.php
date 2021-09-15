<?php

namespace Proximum\Vimeet\Domain\Account;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;

class Synchronizer
{
    /**
     * @var array
     */
    private $tagMapping = [
        Tag::PARTICIPANT_GENDER    => 'Gender',
        Tag::PARTICIPANT_FIRSTNAME => 'FirstName',
        Tag::PARTICIPANT_LASTNAME  => 'LastName',
        Tag::PARTICIPANT_AVATAR    => 'Avatar',
        Tag::PARTICIPANT_POSITION  => 'Position',
        Tag::PARTICIPANT_PHONE     => 'Phone',
        Tag::PARTICIPANT_MOBILE    => 'Mobile',
        Tag::PARTICIPANT_ADDRESS   => 'Address',
        Tag::PARTICIPANT_ZIPCODE   => 'ZipCode',
        Tag::PARTICIPANT_CITY      => 'City',
        Tag::PARTICIPANT_COUNTRY   => 'Country',
        Tag::PARTICIPANT_WEBSITE   => 'Website',
        Tag::SHEET_TITLE           => 'Company',
        Tag::SHEET_ORGANIZATION    => 'Company',
        Tag::SHEET_ADDRESS         => 'CompanyAddress',
        Tag::SHEET_ZIPCODE         => 'CompanyZipCode',
        Tag::SHEET_CITY            => 'CompanyCity',
        Tag::SHEET_COUNTRY         => 'CompanyCountry',
        Tag::SHEET_WEBSITE         => 'CompanyWebsite',
        Tag::SHEET_PHONE           => 'CompanyPhone',
    ];

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get information from account
     *
     * @param TemplateData $templateData
     * @param User         $user
     *
     * @return TemplateData
     */
    public function get(TemplateData $templateData, User $user): TemplateData
    {
        $account = $user->getAccount();

        if (null === $account) {
            return $templateData;
        }

        foreach ($templateData->getEditableObjects() as $object) {
            if ($object instanceof ContentObjectInterface && '' === $object->getContentValue()) {
                $tags = $object->getTags();

                foreach ($tags as $tag) {
                    if (isset($this->tagMapping[$tag])) {
                        $method = 'get' . $this->tagMapping[$tag];

                        if ($object instanceof TemplateObject\Nomenclature) {
                            $object->setContentValue($object->getKeyForLabel($account->$method(), $templateData->getLocale()));
                        } else {
                            $object->setContentValue($account->$method());
                        }
                    }
                }
            }
        }

        return $templateData;
    }

    /**
     * Set information to account
     *
     * @param TemplateData $templateData
     * @param User         $user
     */
    public function set(TemplateData $templateData, User $user)
    {
        $account = $user->getAccount();

        foreach ($templateData->getEditableObjects() as $object) {
            if ($object instanceof ContentObjectInterface && '' !== $object->getContentValueLocalize()) {
                $tags = $object->getTags();

                foreach ($tags as $tag) {
                    if (isset($this->tagMapping[$tag])) {
                        $method = 'set' . $this->tagMapping[$tag];

                        if ($object instanceof TemplateObject\Nomenclature) {
                            $account->$method($object->getLabelForKey($object->getContentValueLocalize(), $templateData->getLocale()));
                        } else {
                            $account->$method($object->getContentValueLocalize());
                        }
                    }
                }
            }
        }

        $this->userRepository->set($user);
    }
}
