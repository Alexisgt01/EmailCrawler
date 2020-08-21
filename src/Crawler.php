<?php


namespace App;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;


class Crawler
{
    public $urls = [];

    public $guzzle;

    public $content;

    public $base_url;

    protected $regex_email = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';

    public $result = '';

    public function __construct($urls, $guzzle)
    {
        $this->urls   = $urls;
        $this->guzzle = $guzzle;
    }

    public function run()
    {
        $this->result .= "URL,Email Address \n";
        foreach ($this->urls as $url) {
            $parsed         = parse_url($url);
            $this->base_url = $parsed['scheme'] . '://' . $parsed['host'];
            $this->request($url);
        }
        file_put_contents(ROOT . DIRECTORY_SEPARATOR . date("Y-m-d@H-m-s") . ".csv", $this->result);
    }

    public function request($url)
    {
        try {
            $res           = $this->guzzle->get($url);
            $this->content = $res->getBody()->getContents();
            $this->extract($url);
        } catch (RequestException | ConnectException | GuzzleException $e) {
            echo "error $url \n";
        }
    }

    public function extract($url)
    {
        preg_match($this->regex_email, $this->content, $match);
        if ($match):
            $this->result .= $url;
            $this->result .= ",";
            $this->result .= $match[0];
            $this->result .= "\n";
            echo "we find : " . $match[0] . "\n";
        endif;
    }
}
