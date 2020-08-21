<?php

namespace App;

use GuzzleHttp\Client;

class Search
{

    public $guzzle;

    public $search;

    public $content;

    public $href = '/<a[^>]* href="([^"]*)"/';

    public $result = [];

    public $nb_per_page = [];

    public $google_subdomain = [
        'support',
        'maps',
        'accounts',
        'www',
        'policies',
        '',
        'webcache',
    ];

    public function __construct()
    {
        $this->guzzle = new Client([
            'headers' => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept'          => '*/*',
                'referer'         => 'https://google.com',
            ]
        ]);
    }

    public function run()
    {
        $this->getArgv();
        $this->request();
        $this->extract();
        $this->requestPerPage();
        $crawler = new Crawler($this->result, $this->guzzle);
        $crawler->run();
    }

    private function getArgv()
    {
        global $argv;
        $this->search = $argv[1];
    }

    /**
     * First request
     */
    public function request()
    {
        $content       = $this->guzzle->get('https://google.com/search?q=' . $this->search);
        $this->content = $content->getBody()->getContents();
    }

    /**
     * First Extraction
     */
    public function extract()
    {
        preg_match_all($this->href, $this->content, $match);
        foreach ($match[1] as $ma) {
            if (strpos($ma, 'http') === 0 && $this->google_checker($ma)) {
                $this->result[] = $ma;
            } else {
                $nb_per_page = intval(self::get_string_between($ma, 'start=', '&'));
                if (is_int($nb_per_page) && !empty($nb_per_page)) {
                    $this->nb_per_page[] = $nb_per_page;
                }
            }
        }
    }

    /**
     * Request per page extracted
     */
    public function requestPerPage()
    {
        foreach ($this->nb_per_page as $i) {
            $content       = $this->guzzle->get('https://google.com/search?q=' . $this->search . '&start=' . $i);
            $this->content = $content->getBody()->getContents();
            $this->extractPerPage();
        }
    }

    public function extractPerPage()
    {
        preg_match_all($this->href, $this->content, $match);
        foreach ($match[1] as $ma) {
            if (strpos($ma, 'http') === 0 && $this->google_checker($ma)) {
                $this->result[] = $ma;
            }
        }
    }

    public function google_checker($url)
    {
        foreach ($this->google_subdomain as $sub) {
            if (is_int(strpos($url, "https://$sub.google")) || is_int(strpos($url, "http://$sub.google")))
                return false;
        }
        return true;
    }

    public static function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini    = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
