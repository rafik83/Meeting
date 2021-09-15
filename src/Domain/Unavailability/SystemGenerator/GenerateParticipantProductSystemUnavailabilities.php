<?php

namespace Proximum\Vimeet\Domain\Unavailability\SystemGenerator;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class GenerateParticipantProductSystemUnavailabilities
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var Generator */
    private $generator;

    public function __construct(UserRepositoryInterface $userRepository, Generator $generator)
    {
        $this->userRepository = $userRepository;
        $this->generator = $generator;
    }

    public function __invoke(Product $product)
    {
        $usersByProduct = $this->userRepository->findByParticipantProduct($product);

        foreach ($usersByProduct as $user) {
            $this->generator->generateSystemUnavailability($product->getEvent(), $user);
        }
    }
}
