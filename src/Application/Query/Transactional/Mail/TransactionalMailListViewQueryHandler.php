<?php

namespace Proximum\Vimeet\Application\Query\Transactional\Mail;

use Proximum\Vimeet\Application\Query\Transactional\Mail\Customize\CustomizedMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Customize\CustomizedMailViewQueryHandler;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Transactional\Mail\MailView;
use Proximum\Vimeet\Application\View\Transactional\Mail\TransactionalMailListView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class TransactionalMailListViewQueryHandler
{
    /** @var GenericMailViewQueryHandler */
    private $genericMailViewQueryHandler;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var CustomizedMailViewQueryHandler */
    private $customizedMailViewQueryHandler;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        GenericMailViewQueryHandler $genericMailViewQueryHandler,
        CustomizedMailViewQueryHandler $customizedMailViewQueryHandler,
        TypeRepositoryInterface $typeRepository,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->genericMailViewQueryHandler = $genericMailViewQueryHandler;
        $this->customizedMailViewQueryHandler = $customizedMailViewQueryHandler;
        $this->messageRepository = $messageRepository;
        $this->typeRepository = $typeRepository;
    }

    public function handle(TransactionalMailListViewQuery $query): TransactionalMailListView
    {
        $locale = $query->event->getAvailableLocale($query->locale);
        $types = $this->typeRepository->getTypesByEvent($query->event);
        $mails = [];

        $messages = $this->messageRepository->findByEvent($query->event);

        foreach ($messages as $message) {
            $key = $message->getType();
            $customizedMail = $this->customizedMailViewQueryHandler->handle(new CustomizedMailViewQuery(
                $message,
                $locale
            ));

            $mails[$key]['messages'][] = $customizedMail;

            foreach ($customizedMail->associatedTypeTitles as $typeId => $typeTitle) {
                $mails[$key]['typesUsed'][$typeId] = $typeTitle;
            }
        }

        $list = [];

        foreach (Constant::TRANSACTIONAL_MAIL_LIST as $key => $data) {
            if (isset($data['isHidden']) && true === $data['isHidden']) {
                continue;
            }

            $typesUsed = $mails[$key]['typesUsed'] ?? [];
            $remainingTypes = array_filter($types, function (Type $type) use ($typesUsed) {
                return !isset($typesUsed[$type->getId()]);
            });

             $generic = $this->genericMailViewQueryHandler->handle(new GenericMailViewQuery(
                $locale,
                $key,
                $data,
                $remainingTypes
            ));

            $list[] = new MailView(
                $key,
                $data['isCustomizableByType'],
                $generic,
                $mails[$key]['messages'] ?? []
            );
        }

        uasort($list, function (MailView $mailViewOne, MailView $mailViewTwo) {
            return $mailViewOne->isCustomizableByType <=> $mailViewTwo->isCustomizableByType;
        });

        return new TransactionalMailListView($list);
    }
}
