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
class FileCreator
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
     * Creates and writes data in to the file
     *
     *
     * @param string The local path of the target file.
     *
     * @return Generator|Object[]
     */
    private function createTheFile($path)
    {
        $handleNew = fopen($path, "wb");
        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <Root>
        <Users>';
        try {
            for ($iterator = 0; $iterator < $this->nodeCount; $iterator++) {
                $age = substr((string)$iterator, -2);
                $xml .= "
            <user>
                <id>$iterator</id>
                <name>User One</name>
                <email>user@mail.com</email>
                <age>$age</age>
                <Ratings>6.2</Ratings>
            </user>";
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
        foreach ($this->createTheFile($this->path) as $line) {
//            var_dump($line);
        }
    }

}

// TODO: Maximum execution time 180sec now
// TODO: Allowed memory size of 1610612736 bytes exhausted