<?php

namespace App\Repository;

use CreamIO\UserBundle\Entity\APIToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method APIToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method APIToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method APIToken[]    findAll()
 * @method APIToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class APITokenRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, APIToken::class);
    }
}
