<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return Product[] */
    public function findActiveProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.supplier', 's')
            ->andWhere('p.isActive = :active')
            ->andWhere('s.isApproved = :approved')
            ->setParameter('active', true)
            ->setParameter('approved', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Product[] */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.supplier', 's')
            ->andWhere('p.category = :category')
            ->andWhere('p.isActive = :active')
            ->andWhere('s.isApproved = :approved')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->setParameter('approved', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Product[] */
    public function findBySupplier(Supplier $supplier): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.supplier = :supplier')
            ->setParameter('supplier', $supplier)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Product[] */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.supplier', 's')
            ->andWhere('LOWER(p.name) LIKE LOWER(:query) OR LOWER(p.description) LIKE LOWER(:query)')
            ->andWhere('p.isActive = :active')
            ->andWhere('s.isApproved = :approved')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('active', true)
            ->setParameter('approved', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Product[] */
    public function findLatest(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.supplier', 's')
            ->andWhere('p.isActive = :active')
            ->andWhere('s.isApproved = :approved')
            ->setParameter('active', true)
            ->setParameter('approved', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
