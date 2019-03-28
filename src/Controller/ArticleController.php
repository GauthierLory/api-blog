<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\Comment;
use App\EventListener\DoctrineEvent;
use App\Form\ImagearticleType;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ImageRepository;
use App\Repository\ArticleLikeRepository;
use App\Repository\ArticleRepository;
use App\Service\HistoriqueHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/article")
 * @IsGranted("ROLE_USER")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET","POST"})
     */
    public function new(Request $request, ObjectManager $manager, ImageRepository $imageRepository): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setCreatedAt(new \DateTime());
//            foreach ($article->getImages() as $image){
//                $image->setarticle($article);
//                $manager->persist($image);
//            }
            $article->setUser($this->getUser());
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'images' => $imageRepository->findAll()
        ]);
    }

    /**
     * @Route("/{slug}/{id}", name="article_show", methods={"GET","POST"})
     */
    public function show($slug, ArticleRepository $repo, Request $request): Response
    {
        $article = $repo->findOneBySlug($slug);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $comment->setAuthor($this->getUser());
                $comment->setArticle($article);
//                dump($form);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($comment);
                $entityManager->flush();

        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/{id}/edit", name="article_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER') and user == article.getUser()", message="This article is not yours")
     */
    public function edit(Request $request, Article $article, ObjectManager $manager, $slug, ArticleRepository $repo, HistoriqueHelper $historiqueHelper, DoctrineEvent $devent): Response
    {

        $article = $repo->findOneBySlug($slug);
        $old_article = "old article";

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($request->request->get('edit')) {
                $this->getDoctrine()->getManager()->flush();

                $t = $devent->preUpdate();
                $new_article = "new roduct";
                $historique = $historiqueHelper->new_historique($this->getUser());
                $historiqueHelper->new_modif($table="user", $champ="atta", $t, $new_article, $historique, $article->getId());

                return $this->redirectToRoute('article_index', [
                    'id' => $article->getId(),
                ]);
            }
            $manager->persist($article);
            $manager->flush();
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }

    /**
     *
     * @Route("/like/{id}/article", name="article_like")
     * @param Article $article
     * @param ObjectManager $manager
     * @param ArticleLikeRepository $likeRepo
     * @return Response
     */
    public function like(Article $article, ObjectManager $manager, ArticleLikeRepository $likeRepo): Response {
        $user = $this->getUser();

        if (!$user) return $this->json([
            'code' => 403,
            'message' => "Unauthorized"
        ], 403);

        if ($article->isLikedByUser($user)){
            $like = $likeRepo->findOneBy([
                'article' => $article,
                'user' => $user,
            ]);
            $manager->remove($like);
            $manager->flush();

            /*return $this->json([
                'code' => 200,
                'message' => 'like bien supprimé',
                'likes' => $likeRepo->count(['article' => $article])
            ], 200);*/
            return $this->redirectToRoute('home');
        }

        $like = new ArticleLike();
        $like->setarticle($article)
            ->setUser($user);

        $manager->persist($like);
        $manager->flush();

        /*return $this->json([
            'code' => 200,
            'message' => 'Ca marche bien',
            'likes' => $likeRepo->count(['article' => $article])
        ], 200);*/

        return $this->redirectToRoute('home');
    }
}