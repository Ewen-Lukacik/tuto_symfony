<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    /**
     * READ ALL
     */
    #[Route('/category', name: 'category.index')]
    public function index(Request $request, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * CREATE  
     */
    #[Route('/category/create', 'category.create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'New category successfully added');
            return $this->redirectToRoute('category.index');
        }

        return $this->render('category/create.html.twig', [
            'form' => $form
        ]);
    }

    
    /**
     * READ ONE
     */
    #[Route('/category/{slug}-{id}', 'category.show', [
        'id' => '\d+',
        'slug' => '[a-z0-9-]+'
    ])]
    public function show(CategoryRepository $categoryRepository, string $slug, int $id)
    {
        $category = $categoryRepository->find($id);
        

        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * EDIT
     */
    #[Route('/category/{id}/edit', 'category.edit', [
        'id' => '\d+'
    ], methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $em)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->flush();
            $this->addFlash('success', 'The category has been updated successfully');
            return $this->redirectToRoute('category.index');
        }
        

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form
        ]);
    }

    /**
     * DELETE
     */
    #[Route('/category/{id}/edit', 'category.delete', methods: ['DELETE'])]
    public function delete(Category $category, EntityManagerInterface $em)
    {
        $em->remove($category);
        $em->flush();
        $this->addFlash('success', 'The category has been deleted successfully');
        return $this->redirectToRoute('category.index');
    }
}
