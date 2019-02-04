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
 * @since 0.0.1
 */

class FileCreator
{
    /**
     * Path for target file
     *
     * @var string
     */
    private $path;

    /**
     * Initialization file params.
     *
     * @param string $path The local path of the target file.
     * @since 0.0.1
     */
    function __construct($path = 'test.xml')
    {
        $this->path = $path;
    }


    /**
     * Creates and writes data in to the file
     *
     * @since 0.0.1
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
            while ($this->count < 3) {
                $this->count++;
                $age = substr((string)$this->count, -2);
                $xml .= "
            <user>
                <id>$this->count</id>
                <name>User One</name>
                <email>user@mail.com</email>
                <age>$age</age>
                <Ratings>6.2</Ratings>
            </user>";
                yield;
            }
        }catch (Exception $e){
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }finally {
            $xml .= "\n\t</Users>\n</Root>";
            fwrite($handleNew, $xml);
            fclose($handleNew);
        }
    }

    /**
     * Runs generation of file
     *
     * @since 0.0.1
     */
    public function run()
    {
        foreach ($this->createTheFile($this->path) as $line) {
//            var_dump($line);
        }
    }

}