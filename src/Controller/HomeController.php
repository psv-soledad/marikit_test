<?php

namespace JobTest\Controller;

use JobTest\Model\ArticleRepository;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class HomeController
 * @package JobTest\Controller
 */
class HomeController
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
     * HomeController constructor.
     * @param ArticleRepository $repository
     * @param Environment $twig
     */
    public function __construct(ArticleRepository $repository, Environment $twig)
    {
        $this->repository = $repository;
        $this->twig = $twig;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        echo $this->twig->render('home.twig', [
            'articles' => $this->repository->getArticles(),
        ]);
    }
}
