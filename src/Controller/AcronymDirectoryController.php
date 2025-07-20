<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class AcronymDirectoryController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('acronym_directory/home.html.twig');
    }


    #[Route('/directory', name: 'acronym_directory_index')]
    public function directory(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('acronym_directory/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/flashcards', name: 'flashcards')]
    public function flashcards(CategoryRepository $categoryRepository, Request $request): Response
    {
        $categories = $categoryRepository->findAll();
        $mode = $request->query->get('mode', 'study');

        return $this->render('acronym_directory/flashcards.html.twig', [
            'categories' => $categories,
            'mode' => $mode,
        ]);
    }


}
