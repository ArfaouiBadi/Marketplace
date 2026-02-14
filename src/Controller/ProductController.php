<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_list')]
    public function list(Request $request, ProductService $productService, CategoryRepository $categoryRepository): Response
    {
        $search = $request->query->get('q', '');
        $categoryId = $request->query->getInt('category', 0);

        if ($search) {
            $products = $productService->search($search);
        } elseif ($categoryId) {
            $category = $categoryRepository->find($categoryId);
            $products = $category ? $productService->getByCategory($category) : [];
        } else {
            $products = $productService->getAll();
        }

        return $this->render('product/list.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $categoryId,
            'searchQuery' => $search,
        ]);
    }

    #[Route('/products/{id}', name: 'product_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ProductService $productService): Response
    {
        $product = $productService->getById($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/category/{slug}', name: 'category_show')]
    public function categoryShow(string $slug, CategoryRepository $categoryRepository, ProductService $productService): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);
        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        return $this->render('product/list.html.twig', [
            'products' => $productService->getByCategory($category),
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $category->getId(),
            'searchQuery' => '',
        ]);
    }

    #[Route('/api/products', name: 'api_products')]
    public function api(ProductService $productService): Response
    {
        return $this->json($productService->getAll());
    }
}
