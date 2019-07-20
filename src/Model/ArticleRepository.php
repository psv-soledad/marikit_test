<?php

namespace JobTest\Model;

/**
 * Interface ArticleRepository
 * @package JobTest\Model
 */
interface ArticleRepository
{
    /**
     * @return Article[]
     */
    public function getArticles(): array;

    /**
     * @param int $id
     * @return Article|null
     */
    public function getArticle(int $id): ?Article;

    /**
     * @param Article $article
     * @return mixed
     */
    public function addArticle(Article $article): bool;
}
