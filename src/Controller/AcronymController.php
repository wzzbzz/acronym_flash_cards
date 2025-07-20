<?php

namespace App\Controller;

use App\Entity\Acronym;
use App\Form\AcronymType;
use App\Repository\AcronymRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/acronym')]
final class AcronymController extends AbstractController
{
    #[Route(name: 'app_acronym_index', methods: ['GET'])]
    public function index(AcronymRepository $acronymRepository): Response
    {
        return $this->render('acronym/index.html.twig', [
            'acronyms' => $acronymRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_acronym_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $acronym = new Acronym();
        $form = $this->createForm(AcronymType::class, $acronym);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($acronym);
            $entityManager->flush();

            return $this->redirectToRoute('app_acronym_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('acronym/new.html.twig', [
            'acronym' => $acronym,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_acronym_show', methods: ['GET'])]
    public function show(Acronym $acronym): Response
    {
        return $this->render('acronym/show.html.twig', [
            'acronym' => $acronym,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_acronym_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Acronym $acronym, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AcronymType::class, $acronym);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_acronym_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('acronym/edit.html.twig', [
            'acronym' => $acronym,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_acronym_delete', methods: ['POST'])]
    public function delete(Request $request, Acronym $acronym, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$acronym->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($acronym);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_acronym_index', [], Response::HTTP_SEE_OTHER);
    }
}
