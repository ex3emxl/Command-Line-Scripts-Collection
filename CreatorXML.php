<?php
/**
 * Generator of XML test file
 *
 * Creates an XML file with a specific number of lines according to a predefined template
 *
 */

/**
 * Class used to generator test XML file
 *
 * @since 1.0.0
 */
class CreatorXML
{
    /**
     * Path for target file
     *
     * @var string
     */
    private $path = '';

    /**
     * Count of generate XML node
     *
     * @var string
     */
    private $nodeCount = 0;

    /**
     * Params of generate file.
     *
     * @param int $nodeCount Count of generate node inside the file.
     * @param string $path The local path of the target file.
     */
    function __construct($nodeCount, $path = 'test.xml')
    {
        $this->path = $path;
        $this->nodeCount = $nodeCount;
    }

    /**
     *
     */
    private function getMemoryLimit()
    {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
            } else if ($matches[2] == 'K') {
                $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
            }
        }
        return $memory_limit;
    }


    /**
     * Creates and writes data in to the file
     *
     *
     * @param string The local path of the target file.
     *
     * @return Generator
     */
    private function createTheFile($path)
    {
        $memory_border = round($this->getMemoryLimit() / 5);
        $handleNew = fopen($path, "wb");
        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <Root>
        <Users>';
        try {
            for ($iterator = 0; $iterator < $this->nodeCount; $iterator++) {
                $id = $iterator + 1;
                $age = substr((string)$id, -2);
                $xml .= "
            <user>
                <id>$id</id>
                <name>User One</name>
                <email>user@mail.com</email>
                <age>$age</age>
                <Ratings>6.2</Ratings>
            </user>";
                if ($memory_border < memory_get_usage()) {
                    fwrite($handleNew, $xml);
                    fclose($handleNew);
                    $handleNew = fopen($path, "ab");
                    $xml = '';
                }
                yield;
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        } finally {
            $xml .= "\n\t</Users>\n</Root>";
            fwrite($handleNew, $xml);
            fclose($handleNew);
        }
    }

    /**
     * Runs generation of file
     *
     */
    public function run()
    {
        $start = microtime(true);
        foreach ($this->createTheFile($this->path) as $line) {
//            var_dump($line);
        }
        echo 'Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';
    }

}

$testFile = new CreatorXML(20000);
$testFile->run();

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

//print formatBytes(memory_get_peak_usage());