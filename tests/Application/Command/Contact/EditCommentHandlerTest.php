<?php

namespace Proximum\Vimeet\Tests\Application\Command\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Contact\EditComment;
use Proximum\Vimeet\Application\Command\Contact\EditCommentHandler;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EditCommentHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $contact = $this->prophesize(Contact::class);

        // prophecies dependencies
        $contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $contact->setComment('comment')
            ->shouldBeCalled()
        ;
        $contactRepository->set($contact)->shouldBeCalled();

        // run tests
        $query = new EditComment($contact->reveal(), 'comment');

        $editCommentHandler = new EditCommentHandler($contactRepository->reveal());
        $editCommentHandler->handle($query);
    }
}
