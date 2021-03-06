<?php

namespace IGN;

use Buzz\Browser;
use Symfony\Component\DomCrawler\Crawler;

class Game
{
    protected $title;
    protected $url;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * Constructor
     *
     * @param $imdbId
     * @param null $title
     */
    public function __construct($url)
    {
        $this->title = null;
        $this->url   = $url;
    }

    public function getTitle()
    {
        if (null === $this->title) {
            try {
                $this->title = trim($this->getCrawler()->filterXpath('//h1[@class="contentTitle"]')->text());
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->title;
    }

    public function getReleaseDate()
    {
        try {
            return $this->getCrawler()->filterXpath("//div[@class='releaseDate']/strong")->text();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getReleaseDateUnixtime()
    {
        try {
            $releaseDate = $this->getReleaseDate();
            return strtotime($releaseDate);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getYear()
    {
        try {
            $unixtime = $this->getReleaseDateUnixtime();
            return date("Y", $unixtime);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSummary()
    {
        try {
            $summary = '';
            $this->getCrawler()->filterXpath("//div[@id='summary']/div[@class='gameInfo']//p")->each(function ($node, $i) use (&$summary) {
                $summary .= htmlentities($node->nodeValue);
            });
            return trim(html_entity_decode($summary));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSummarySplit()
    {
        try {
            $summary = array();
            $this->getCrawler()->filterXpath("//div[@id='summary']/div[@class='gameInfo']/p/..")->each(function ($node, $i) use (&$summary) {
                $summary[] = trim(html_entity_decode(htmlentities($node->nodeValue)));
            });
            return $summary;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getRating()
    {
        try {
            return trim($this->getCrawler()->filter('.ignRating div.ratingValue')->text());
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getGenres()
    {
        $genres = array();

        try {
            $this->getCrawler()->filterXpath("//div[@class='gameInfo-list']/div/strong[text()='Genre']/../a[contains(@href, '/games/editors-choice')]")->each(function ($node, $i) use (&$genres) {
                $genres[] = trim(strip_tags($node->nodeValue));
            });
        } catch (\Exception $e) {
        }

        return $genres;
    }

    public function getPublishers()
    {
        $publishers = array();

        try {
            $this->getCrawler()->filterXpath("//div[@class='gameInfo-list']/div/strong[text()='Publisher']/../a")->each(function ($node, $i) use (&$publishers) {
                $publishers[] = trim(strip_tags($node->nodeValue));
            });
        } catch (\Exception $e) {
        }

        return $publishers;
    }

    public function getDevelopers()
    {
        $developers = array();

        try {
            $this->getCrawler()->filterXpath("//div[@class='gameInfo-list']/div/strong[text()='Developer']/../a")->each(function ($node, $i) use (&$developers) {
                $developers[] = trim(strip_tags($node->nodeValue));
            });
        } catch (\Exception $e) {
        }

        return $developers;
    }

    /**
     * @return Crawler
     */
    protected function getCrawler()
    {
        if (null === $this->crawler) {
            $client = new Browser();

            $this->crawler = new Crawler($client->get($this->url)->getContent());
        }

        return $this->crawler; 
    }
}
