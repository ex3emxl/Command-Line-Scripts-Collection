<?php

/**
 * Class for collect data from command line
 *
 * @since 1.0.0
 */
class CollectDataFromCommandLine
{

    /**
     * Collection of associated data
     *
     * @var array['url']        string Resource URL.
     */
    private $collection = array(
        'url' => '',
        'tag' => ''
    );

    /**
     * CollectDataFromCommandLine constructor.
     */
    function __construct()
    {
        if (defined('STDIN')) {
            $this->collect();
        }
    }

    /**
     * Collects data typing from command line
     */
    private function collect()
    {
        echo "Please, enter url: ";
        $url = rtrim(fgets(STDIN));
        while (empty(filter_var($url, FILTER_VALIDATE_URL))) {
            echo 'Not a valid URL! Try again: ';
            $url = rtrim(fgets(STDIN));
        }
        $resourceHeaders = get_headers($url, true);
        if (strripos($resourceHeaders['Content-Type'], 'text/html') === false) {
            echo "Resource size: " . $resourceHeaders['Content-Length'] . " Bytes" . "\n";
            exit(0);
        } else {
            echo 'Please, enter resources tag (embed, img, audio, video...): ';
            $tag = rtrim(fgets(STDIN));
        }
        $this->collection = array(
            'url' => $url,
            'tag' => $tag
        );
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

}

class CheckSize
{

    /**
     * URL of resource
     *
     * @var string
     */
    private $url;

    /**
     * Tag of resource
     *
     * @var string
     */
    private $tag;

    /**
     * CheckSize constructor.
     *
     * @param array $params
     */
    function __construct($params)
    {
        if (
            is_array($params)
            && array_key_exists('url', $params)
            && array_key_exists('tag', $params)
        ) {
            $this->url = $params['url'];
            $this->tag = $params['tag'];
        }
    }

    private function checkSize($url, $tag)
    {
        $html = file_get_contents($url);
        $dom = new DomDocument();
        @$dom->loadHTML($html);
        $para = $dom->getElementsByTagName($tag); #DOMNodeList

        $fullSize = strlen($html);
        $countOfRequest = 2;

        if ($para instanceof DOMNodeList) {
            foreach ($para as $node) {
                $src = filter_var($node->getAttribute('src'), FILTER_VALIDATE_URL);
                if (!empty($src)) {
                    $headers = get_headers($src, true);
                    $countOfRequest++;
                    if ((int)substr($headers[0], 9, 3) == 200) {
                        $fullSize += (int)$headers['Content-Length'];
                        echo $src . " size " . $headers['Content-Length'] . " Bytes\n";
                    }
                }
            }
        }
        echo "Size all resources: " . $fullSize . " Bytes\n";
        echo "Count of requests: " . $countOfRequest;

    }

    /**
     * Runs resource scanner and fix value.
     */
    public function run()
    {
        $this->checkSize($this->url, $this->tag);
    }

}

$collectionData = new CollectDataFromCommandLine();

$targetFile = new CheckSize($collectionData->collection);

$targetFile->run();

