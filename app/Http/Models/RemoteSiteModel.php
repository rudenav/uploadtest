<?php

namespace App\Http\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Psr7\Response;

class RemoteSiteModel
{
    /**
     * @var string
     */
    protected $baseUrl = 'http://ourdesigngroup.com/';

    /**
     * @var string
     */
    protected $getFormUrl = 'photos/new';

    /**
     * @var string
     */
    protected $postFormUrl = 'photos';

    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var string
     */
    protected $formAuthKey = '';

    /**
     * @var Response;
     */
    private $response = '';

    /**
     * @var FileCookieJar
     */
    private $cookies;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var Guzzle
     */
    private $requester;

    /**
     * RemoteSiteModel constructor.
     * @param Client $requester
     */
    public function __construct(Client $requester)
    {
        $this->requester = $requester;
        $cookiesDir = storage_path('cookies/');
        $cookiesFilename = str_random() . '.json';
        $this->cookies = new FileCookieJar($cookiesDir . $cookiesFilename);
    }

    /**
     * @param array $options
     * @return $this
     */
    public function fetchForm($options = [])
    {
        $options['cookies'] = $this->cookies;
        $this->response = $this->requester->request(
            'GET',
            $this->baseUrl . $this->getFormUrl,
            array_merge($options, $this->options)
        );
        return $this;
    }

    public function sendForm($imagePath = '', $title = '')
    {
        $options = [
            'multipart' => [
                [
                    'name' => 'utf8',
                    'contents' => '&#x2713;',
                ],
                [
                    'name' => 'authenticity_token',
                    'contents' => $this->formAuthKey,
                ],
                [
                    'name' => 'photo[title]',
                    'contents' => $title,
                ],
                [
                    'name' => 'photo[image]',
                    'contents' => fopen($imagePath, 'r'),
                    'filename' => str_random().'.gif',
                ],
            ],
            'cookies' => $this->cookies,
        ];
        $result = $this->requester
            ->request(
                'POST',
                $this->baseUrl . $this->postFormUrl,
                array_merge($options, $this->options)
            );
    }

    /**
     * @return $this
     */
    public function extractAuthKey()
    {
        $this->formAuthKey = $this->makeDom()->getAuthKey();

        return $this;
    }

    /**
     * @return $this
     */
    private function makeDom()
    {
        $this->dom = new \DOMDocument();
        try {
            libxml_use_internal_errors(true);
            $this->dom->loadHTML($this->response->getBody()->getContents());
            libxml_clear_errors();
        } catch (\Exception $e) {
            //
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getAuthKey()
    {
        $authKeyQuery = '//input[@name="authenticity_token"]/@value';
        $DomXpath = new \DOMXPath($this->dom);
        $nodes = $DomXpath->query($authKeyQuery, null);
        return $nodes->length > 0 ? $nodes[0]->nodeValue : '';
    }
}