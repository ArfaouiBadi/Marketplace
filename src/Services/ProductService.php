<?php

namespace App\Services;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Supplier;
use App\Repository\ProductRepository;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
    ) {}

    /** @return Product[] */
    public function getAll(): array
    {
        return $this->productRepository->findActiveProducts();
    }

    public function getById(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function getBySlug(string $slug): ?Product
    {
        return $this->productRepository->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    /** @return Product[] */
    public function getByCategory(Category $category): array
    {
        return $this->productRepository->findByCategory($category);
    }

    /** @return Product[] */
    public function getBySupplier(Supplier $supplier): array
    {
        return $this->productRepository->findBySupplier($supplier);
    }

    /** @return Product[] */
    public function search(string $query): array
    {
        return $this->productRepository->search($query);
    }

    /** @return Product[] */
    public function getLatest(int $limit = 8): array
    {
        return $this->productRepository->findLatest($limit);
    }

    public function save(Product $product): void
    {
        $this->productRepository->save($product, true);
    }

    public function remove(Product $product): void
    {
        $this->productRepository->remove($product, true);
    }
}