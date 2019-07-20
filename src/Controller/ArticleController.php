<?php

namespace JobTest\Controller;

use JobTest\Model\ArticleRepository;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ArticleController
 * @package JobTest\Controller
 */
class ArticleController
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * ArticleController constructor.
     * @param ArticleRepository $repository
     * @param Environment $twig
     */
    public function __construct(ArticleRepository $repository, Environment $twig)
    {
        $this->repository = $repository;
        $this->twig = $twig;
    }

    /**
     * @param $id
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function show($id): void
    {
        $article = $this->repository->getArticle($id);
        if (!$article) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }
        echo $this->twig->render('article.twig', [
            'article' => $article,
        ]);
    }
}
