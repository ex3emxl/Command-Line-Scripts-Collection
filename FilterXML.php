<?php

/**
 * Class FilterData
 */
class FilterXML
{

    /**
     * @var string
     */
    private $path;
    /**
     * @var int
     */
    private $marker = 0;
    /**
     * @var string
     */
    private $user_node = '';

    /**
     * FilterData constructor.
     * @param string $path
     */
    function __construct($path = '')
    {
        $this->path = $path;
    }

    /**
     * @param $path
     * @return Generator
     */
    private function readTheFile($path)
    {
        $srcXML = fopen($path, "r");
        $targetXML = fopen('target.xml', "w");

        while (!feof($srcXML)) {
            $ligneXML = fgets($srcXML);
            if (preg_match("/\<user\>/", $ligneXML)) {
                $this->marker = 1;
            }
            if ($this->marker == 1) {
                preg_match_all("/\<age\>(.*?)\<\/age\>/s", $ligneXML, $matches);
                $this->user_node .= $ligneXML;
                if (isset($matches[1][0]) && ($matches[1][0] > 30 || $matches[1][0] < 20)) {
                    $this->user_node = '';
                    $this->marker = 0;
                    yield;
                }
            }
            if (preg_match("/\<\/user>/", $ligneXML)) {
                fwrite($targetXML, $this->user_node);
                $this->marker = 0;
                $this->user_node = '';
                yield;
            }
        }

        fclose($srcXML);
    }

    /**
     *
     */
    public function run()
    {
        foreach ($this->readTheFile($this->path) as $line) {
            $line;
        }
    }

}



$newFile = new FilterXML('test.xml');
$newFile->run();


/**
 * @param $bytes
 * @param int $precision
 * @return string
 */
function formatBytes($bytes, $precision = 2)
{
    $units = array("b", "kb", "mb", "gb", "tb");

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . " " . $units[$pow];
}

print formatBytes(memory_get_peak_usage());
