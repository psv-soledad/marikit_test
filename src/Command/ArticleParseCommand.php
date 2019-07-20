<?php

namespace JobTest\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JobTest\Model\Article;
use JobTest\Model\ArticleRepository;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ArticleParseCommand
 * @package JobTest\Command
 */
class ArticleParseCommand
{
    /**
     * Максимальное колличество новостей
     */
    public const MAX_COUNT = 99;
    /**
     * @var Client
     */
    private $clien;

    /**
     * @var ArticleRepository
     */
    private $article_repository;

    /**
     * ArticleParseCommand constructor.
     * @param Client $clien
     * @param ArticleRepository $article_repository
     */
    public function __construct(Client $clien, ArticleRepository $article_repository)
    {
        $this->clien = $clien;
        $this->article_repository = $article_repository;
    }

    /**
     * @param string $text
     * @return string|null
     */
    private function getArticleUrl(string $text): ?string
    {
        $document = \phpQuery::newDocumentHTML($text);
        //Ищем URL
        $url = $document->find('a')->attr('href');
        if (empty($url)) {
            return null;
        }
        $url_data = parse_url($url);
        if ($url_data['query'] !== 'from=newsfeed') {
            //Это не похоже на новость
            return null;
        }
        return $url;
    }

    /**
     * @param string $text
     * @return string|null
     */
    private function getArticleTitle(string $text): ?string
    {
        $document = \phpQuery::newDocumentHTML($text);
        $title = $document->find('.article__header__title span.js-slide-title')->text();
        $title = trim(str_replace(["\n", "\r"], '', $title));
        if (empty($title)) {
            return null;
        }
        return $title;
    }

    /**
     * @param string $text
     * @return string|null
     */
    private function getArticleContext(string $text): ?string
    {
        $document = \phpQuery::newDocumentHTML($text);
        $document->find('.gallery_vertical')->remove();
        $document->find('.article__inline-item')->remove();
        $document->find('twitter-widget')->remove();
        $document->find('script')->remove();
        $article_text = $document->find('.article__text')->html();
        $context = strip_tags($article_text);
        $context = str_replace("\n", '', $context);
        $context = preg_replace('/ {2,}/', ' ', $context);
        return empty($context) ? null : $context;
    }

    /**
     * @param string $text
     * @param OutputInterface $output
     * @return string|null
     */
    private function getArticleImg(string $text, OutputInterface $output): ?string
    {
        $document = \phpQuery::newDocumentHTML($text);
        $img_url = $document->find('.article__main-image__wrap img')->attr('src');
        $img = null;
        if ($img_url) {
            try {
                $res = $this->clien->request('GET', $img_url);
            } catch (GuzzleException $e) {
                $output->writeln('Ошибка | Невозможно получить изображение по адресу ' . $img_url);
                return null;
            }
            $img = $res->getBody()->getContents();

            if ($img) {
                $type = pathinfo($img_url, PATHINFO_EXTENSION);
                $img = 'data:image/' . $type . ';base64,' . base64_encode($img);
            } else {
                $img = null;
            }
        }
        return $img;

    }

    /**
     * @param string $article_url
     * @param OutputInterface $output
     * @return string|null
     */
    private function getArticleText(string $article_url, OutputInterface $output): ?string
    {
        //Получаем хеш статьи
        $url_data = parse_url($article_url);
        $path = explode('/', $url_data['path']);
        if ($url_data['host'] === 'sport.rbc.ru') {
            $url = 'https://sportrbc.ru/news/ajax/' . array_pop($path);
        } else {
            $url = 'https://www.rbc.ru/v10/ajax/news/slide/' . array_pop($path);
        }
        try {
            $res = $this->clien->request('GET', $url);
        } catch (GuzzleException $e) {
            $output->writeln('Ошибка | Невозможно получить текст статьи по урлу ' . $article_url);
            return null;
        }
        // получаем данные между открывающим и закрывающим тегами body
        $body = $res->getBody()->getContents();
        $body_data = json_decode($body, true);
        return $body_data['html'] ?? null;
    }

    /**
     * @param int $limit
     * @param string $project
     * @param OutputInterface $output
     */
    public function __invoke(int $limit, string $project, OutputInterface $output)
    {
        // отправляем запрос к странице rbc
        $url = 'https://www.rbc.ru/v10/ajax/get-news-feed/project/' . $project . '/lastDate/' . time() . '/limit/' . self::MAX_COUNT;
        try {
            $res = $this->clien->request('GET', $url);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Ошибка | Невозможно получить данные по урлу ' . $url);
        }

        // получаем данные между открывающим и закрывающим тегами body
        $body = $res->getBody()->getContents();
        $news = json_decode($body, true);
        $data = [];
        if (empty($news['items'])) {
            throw new RuntimeException('Ошибка | Новости не найдены по урлу ' . $url);
        }

        //Формируем массив с новостями
        foreach ($news['items'] as $item) {
            //Ищем урл статьи
            $text = $item['html'] ?? '';
            $url = $this->getArticleUrl($text);
            if ($url === null) {
                continue;
            }
            $novelty['url'] = $url;

            //Получаем текст этой статьи
            $text = $this->getArticleText($url, $output);
            if ($text === null) {
                continue;
            }

            //Получаем Img
            $img = $this->getArticleImg($text, $output);
            $novelty['img'] = $img;

            //Получаем Content
            $context = $this->getArticleContext($text);
            $novelty['content'] = $context;
            if ($context === null) {
                continue;
            }

            //Ищем Title
            $title = $this->getArticleTitle($text);
            $novelty['title'] = $title;

            //Получаем Time
            $novelty['time'] = $item['publish_date_t'];

            //Формируем массив с данными
            $data[] = $novelty;

            //Набрали еобходимое колличество новостей
            if (count($data) === $limit) {
                break;
            }
        }

        //Сохраняем в БД
        foreach ($data as $article_data) {
            $article = new Article();
            $article->setContent($article_data['content']);
            $article->setTitle($article_data['title']);
            $article->setImg($article_data['img']);
            $article->setTime($article_data['time']);
            $res = $this->article_repository->addArticle($article);
            if ($res) {
                $output->writeln('Новость добавлена успешно | ' . $article_data['title']);
            }
        }
    }
}
