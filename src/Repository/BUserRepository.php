<?php

namespace CreamIO\UserBundle\Repository;

use CreamIO\UserBundle\Entity\BUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method BUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method BUser[]    findAll()
 * @method BUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BUserRepository extends ServiceEntityRepository
{
    /**
     * BUserRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BUser::class);
    }
}
