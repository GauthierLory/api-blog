<?php

namespace App\Controller;

use App\Entity\ArticleTag;
use App\Form\ArticleTagType;
use App\Repository\ArticleTagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tag")
 */
class ArticleTagController extends AbstractController
{
    /**
     * @Route("", name="article_tag_index", methods={"GET"})
     */
    public function index(ArticleTagRepository $articleTagRepository): Response
    {
        return $this->render('article_tag/index.html.twig', [
            'article_tags' => $articleTagRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="article_tag_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $articleTag = new ArticleTag();
        $form = $this->createForm(ArticleTagType::class, $articleTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($articleTag);
            $entityManager->flush();

            return $this->redirectToRoute('article_tag_index');
        }

        return $this->render('article_tag/new.html.twig', [
            'article_tag' => $articleTag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_tag_show", methods={"GET"})
     */
    public function show(ArticleTag $articleTag): Response
    {
        return $this->render('article_tag/show.html.twig', [
            'article_tag' => $articleTag,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="article_tag_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ArticleTag $articleTag): Response
    {
        $form = $this->createForm(ArticleTagType::class, $articleTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_tag_index', [
                'id' => $articleTag->getId(),
            ]);
        }

        return $this->render('article_tag/edit.html.twig', [
            'article_tag' => $articleTag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_tag_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ArticleTag $articleTag): Response
    {
        if ($this->isCsrfTokenValid('delete'.$articleTag->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($articleTag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_tag_index');
    }
}
