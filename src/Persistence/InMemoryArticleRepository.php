<?php

namespace JobTest\Persistence;

use JobTest\Model\Article;
use JobTest\Model\ArticleRepository;
use PDO;

/**
 * Class InMemoryArticleRepository
 * @package JobTest\Persistence
 */
class InMemoryArticleRepository implements ArticleRepository
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * InMemoryArticleRepository constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Article[]
     */
    public function getArticles(): array
    {
        $sql = 'SELECT time, img, content, title, id FROM article ORDER BY id';
        $articles = [];
        foreach ($this->pdo->query($sql) as $row) {
            $article_obj = new Article();
            $article_obj->setTime($row['time']);
            $article_obj->setImg($row['img']);
            $article_obj->setTitle($row['title']);
            $article_obj->setContent($row['content']);
            $article_obj->setId($row['id']);
            $articles[] = $article_obj;
        }
        return $articles;
    }

    /**
     * @param int $id
     * @return Article
     */
    public function getArticle(int $id): ?Article
    {
        $sql = 'SELECT time, img, content, title, id FROM article WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $article_data = $stmt->fetch();
        if (!$article_data) {
            return null;
        }
        $article_obj = new Article();
        $article_obj->setTime($article_data['time']);
        $article_obj->setImg($article_data['img']);
        $article_obj->setTitle($article_data['title']);
        $article_obj->setId($article_data['id']);
        $article_obj->setContent($article_data['content']);
        return $article_obj;
    }

    /**
     * @param Article $article
     * @return mixed
     */
    public function addArticle(Article $article): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO article (time, img, content, title) VALUES (:time, :img, :content, :title)');
        $time = $article->getTime();
        $img = $article->getImg();
        $content = $article->getContent();
        $title = $article->getTitle();
        if ($time) {
            $time = date('Y-m-d H:i:s', $time);
        }
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':img', $img);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':title', $title);
        return $stmt->execute();
    }
}
