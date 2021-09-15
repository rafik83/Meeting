<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MessageEntityType extends AbstractType
{
    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * @param MessageRepositoryInterface $messageRepository
     * @param UrlGeneratorInterface      $urlGenerator
     */
    public function __construct(MessageRepositoryInterface $messageRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->messageRepository = $messageRepository;
        $this->urlGenerator      = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'class'        => Message::class,
            'choices'      => function (Options $options) {
                return $this->messageRepository->findByEvent($options['event']);
            },
            'choice_label' => 'name',
            'choice_attr'  => function (Message $message) {
                return [
                    'data-preview-url'     => $this->urlGenerator->generate('admin_messaging_message_preview', [
                        'message' => $message->getId(),
                        'event'   => $message->getEvent()->getId(),
                    ]),
                    'data-message-preview' => 1,
                ];
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
