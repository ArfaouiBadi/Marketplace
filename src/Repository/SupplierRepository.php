<?php

namespace App\Repository;

use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Supplier>
 */
class SupplierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supplier::class);
    }

    public function save(Supplier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Supplier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return Supplier[] */
    public function findPendingApproval(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isApproved = :approved')
            ->setParameter('approved', false)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Supplier[] */
    public function findApproved(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isApproved = :approved')
            ->setParameter('approved', true)
            ->orderBy('s.companyName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
