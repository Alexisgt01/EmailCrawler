<?php


namespace App;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;


class Crawler
{
    /**
     * All url resulted
     * @var array $urls
     */
    public $urls = [];

    /**
     * Guzzle instance
     * @var Client $guzzle ;
     */
    public $guzzle;

    /**
     * Website content
     * @var string $content
     */
    public $content;

    /**
     * Base of website's url
     * @var string $base_url
     */
    public $base_url;

    /**
     * Regex for find email
     * @var string $regex_email
     */
    protected $regex_email = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';

    /**
     * CSV Result
     * @var string $result
     */
    public $result = '';

    /**
     * Word to search ( its name of csv )
     * @var string $filename
     */
    public $filename;

    /**
     * Crawler constructor.
     * @param array $urls
     * @param $guzzle
     * @param string $search
     */
    public function __construct($urls, $guzzle, $search)
    {
        $this->urls     = $urls;
        $this->guzzle   = $guzzle;
        $this->filename = str_replace(' ', '_', $search) . ".csv";
    }

    /**
     * Run the script
     */
    public function run()
    {
        $this->result .= "URL,Email Address \n";
        foreach ($this->urls as $url) {
            $parsed         = parse_url($url);
            $this->base_url = $parsed['scheme'] . '://' . $parsed['host'];
            $this->request($url);
        }
        echo ROOT . DIRECTORY_SEPARATOR . $this->filename . " file generated";
        file_put_contents(ROOT . DIRECTORY_SEPARATOR . $this->filename, $this->result);
    }

    /**
     * Request the website
     * @param string $url
     */
    public function request($url)
    {
        try {
            $res           = $this->guzzle->request('GET', $url, ['connect_timeout' => 15]);
            $this->content = $res->getBody()->getContents();
            $this->extract($url);
        } catch (RequestException | ConnectException | BadResponseException | ServerException | ClientException | GuzzleException $e) {
            echo "error $url \n";
        }
    }

    /**
     * Extract email
     * @param string $url
     */
    public function extract($url)
    {
        preg_match($this->regex_email, $this->content, $match);
        if ($match && filter_var($match[0], FILTER_VALIDATE_EMAIL) && checkdnsrr(explode("@", $match[0])[1], 'MX')):
            $this->result .= $url;
            $this->result .= ",";
            $this->result .= $match[0];
            $this->result .= "\n";
            echo "we find : " . $match[0] . "\n";
        endif;
    }
}
