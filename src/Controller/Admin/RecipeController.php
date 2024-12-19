<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[Route('/admin/recettes', name:'admin.recipe.')]
class RecipeController extends AbstractController
{
    /**
     * @param Request
     * @param RecipeRepository
     */
    #[Route('/', name: 'index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, RecipeRepository $recipeRepository, CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
    
        $page = $request->query->getInt('page', 1);
        $recipes = $recipeRepository->paginateRecipes($page);
 
        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    /**
     * @param Request
     * @param Recipe
     * @param EntityManagerInterface
     */
    #[Route('/{id}', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS] )]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $em, UploaderHelper $helper)
    {

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $this->uploadFile($request, $recipe, $form);
            $em->flush();
            $this->addFlash('success', 'La recette a bien été modifiée');
            return $this->redirectToRoute('admin.recipe.index');
        }
        return $this->render('admin/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form
        ]);
    }

    /**
     * @param Request
     * @param EntityManagerInterface
     */
    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository)
    {
        $recipe = new Recipe(); 
        $categoryId = $categoryRepository->find('1');
        $form = $this->createForm(RecipeType::class, $recipe);
        $recipe->setCategory($categoryId);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            
            $this->uploadFile($request, $recipe, $form);
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La recette a bien été créée');
            return $this->redirectToRoute('admin.recipe.index');
        }
        return $this->render('admin/recipe/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ["DELETE"], requirements: ['id' => Requirement::DIGITS] )]
    public function remove(Recipe $recipe, EntityManagerInterface $em)
    {
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'La recette a bien été supprimée');
        return $this->redirectToRoute('admin.recipe.index');
    }

    public function uploadFile(Request $request, Recipe $recipe, $form){
         /**
             * @var UploadedFile
             */
            $file = $form->get('thumbnailFile')->getData();
            $filename = $recipe->getId() . "-" . $recipe->getSlug() . '.' . $file->getClientOriginalExtension();
            $fileMovedDir = $this->getParameter('kernel.project_dir') . '/public/images/recettes';
            $file->move($fileMovedDir, $filename);
            $recipe->setThumbnail($filename);
    }
}
