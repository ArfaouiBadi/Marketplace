<?php

namespace App\Controller\Supplier;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductFormType;
use App\Services\FileUploadService;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/supplier/products')]
#[IsGranted('ROLE_SUPPLIER')]
class SupplierProductController extends AbstractController
{
    #[Route('', name: 'supplier_products')]
    public function list(ProductService $productService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $supplier = $user->getSupplier();

        return $this->render('supplier/product/list.html.twig', [
            'products' => $productService->getBySupplier($supplier),
        ]);
    }

    #[Route('/new', name: 'supplier_product_new')]
    public function new(Request $request, ProductService $productService, SluggerInterface $slugger, FileUploadService $uploadService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $supplier = $user->getSupplier();

        $product = new Product();
        $product->setSupplier($supplier);

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                try {
                    $filename = $uploadService->uploadProductImage($imageFile);
                    $product->setImage($filename);
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image: ' . $e->getMessage());
                    return $this->redirectToRoute('supplier_product_new');
                }
            }

            $product->setSlug(strtolower($slugger->slug($product->getName())->toString()) . '-' . uniqid());
            $productService->save($product);

            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('supplier_products');
        }

        return $this->render('supplier/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'supplier_product_edit')]
    public function edit(int $id, Request $request, ProductService $productService, SluggerInterface $slugger, FileUploadService $uploadService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $supplier = $user->getSupplier();

        $product = $productService->getById($id);

        if (!$product || $product->getSupplier()->getId() !== $supplier->getId()) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                try {
                    $filename = $uploadService->uploadProductImage($imageFile);
                    $product->setImage($filename);
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image: ' . $e->getMessage());
                    return $this->redirectToRoute('supplier_product_edit', ['id' => $product->getId()]);
                }
            }

            $product->setUpdatedAt(new \DateTimeImmutable());
            $productService->save($product);

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('supplier_products');
        }

        return $this->render('supplier/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'supplier_product_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, ProductService $productService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $supplier = $user->getSupplier();

        $product = $productService->getById($id);

        if (!$product || $product->getSupplier()->getId() !== $supplier->getId()) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $productService->remove($product);
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('supplier_products');
    }
}
