<?php

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class RegisterNewUserHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var SaltGeneratorInterface
     */
    private $saltGenerator;

    /**
     * @var UserEventRepositoryInterface
     */
    private $userEventRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param UserRepositoryInterface      $userRepository
     * @param PasswordEncoderInterface     $encoder
     * @param SaltGeneratorInterface       $saltGenerator
     * @param UserEventRepositoryInterface $userEventRepository
     * @param TypeRepositoryInterface      $typeRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        UserEventRepositoryInterface $userEventRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->userRepository      = $userRepository;
        $this->encoder             = $encoder;
        $this->saltGenerator       = $saltGenerator;
        $this->userEventRepository = $userEventRepository;
        $this->typeRepository      = $typeRepository;
    }

    /**
     * @param RegisterNewUser $register
     *
     * @throws EmailAlreadyExistsException
     *
     * @return RegisterNewUserResult
     */
    public function handle(RegisterNewUser $register)
    {
        if ($this->userRepository->emailExists($register->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $register->email));
        }

        $type     = $this->typeRepository->getById($register->typeView->id);
        $salt     = $this->saltGenerator->generate();
        $user     = new User($register->email, $salt, null, $register->locale);
        $password = $this->encoder->encode($user, $register->password);
        $user->updatePassword($salt, $password);

        $userEvent = new UserEvent($user, $register->event, $type);

        $this->userRepository->add($user);
        $this->userEventRepository->add($userEvent);

        return new RegisterNewUserResult($user);
    }
}
