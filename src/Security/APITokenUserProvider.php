<?php

namespace CreamIO\UserBundle\Security;

use CreamIO\UserBundle\Entity\BUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;

class APITokenUserProvider implements UserProviderInterface
{
    protected $apiTokenRepository;
    protected $userRepository;

    public function __construct(EntityRepository $apiTokenRepository, EntityRepository $userRepository)
    {
        $this->apiTokenRepository = $apiTokenRepository;
        $this->userRepository = $userRepository;
    }

    public function getAuthToken($authTokenHeader)
    {
        return $this->apiTokenRepository->findOneByHash($authTokenHeader);
    }

    public function loadUserByUsername($email)
    {
        return $this->userRepository->findOneByUsername($email);
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return BUser::class === $class;
    }
}