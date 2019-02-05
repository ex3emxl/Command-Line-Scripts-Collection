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
    private $node = '';
    /**
     * @var string
     */
    private $lowAge = '';
    /**
     * @var string
     */
    private $highAge = '';


    /**
     * FilterData constructor.
     * @param string $path
     */
    function __construct($path = '', $lowAge, $highAge)
    {
        $this->path = $path;
        $this->lowAge = $lowAge;
        $this->highAge = $highAge;
        $this->node = "<?xml version='1.0' encoding='utf-8'?>\n<Users>\n";
    }

    /**
     * @param $path
     * @return Generator
     */
    private function readTheFile($path)
    {
        $srcXML = fopen($path, "r");
        $targetXML = fopen('target.xml', "wb");
        fwrite($targetXML, $this->node);

        while (!feof($srcXML)) {
            $lineXML = fgets($srcXML);
            if (preg_match("/\<user\>/", $lineXML)) {
                $this->marker = 1;
            }
            if ($this->marker == 1) {
                preg_match_all("/\<age\>(.*?)\<\/age\>/s", $lineXML, $matches);
                $this->node .= $lineXML;
                if (isset($matches[1][0]) && ($matches[1][0] > $this->highAge || $matches[1][0] < $this->lowAge)) {
                    $this->node = '';
                    $this->marker = 0;
                    yield;
                }
            }
            if (preg_match("/\<\/user>/", $lineXML)) {
                fwrite($targetXML, $this->node);
                $this->marker = 0;
                $this->node = '';
                yield;
            }
        }

        $this->node .= "</Users>";
        fwrite($targetXML, $this->node);
        fclose($targetXML);
        fclose($srcXML);
    }

    /**
     * Runs generation of file
     *
     */
    public function run()
    {
        $start = microtime(true);

        //for high speed we not be show any data like progress bar
        foreach ($this->readTheFile($this->path) as $line) {
        }
        echo 'Time of script execution: ' . round(microtime(true) - $start, 4) . ' sec.' . "\n";
    }

}


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


if (defined('STDIN')) {
    do {
        echo "Please, enter relative path to the file: ";
        $path = rtrim(fgets(STDIN));
        while (!file_exists($path)) {
            echo "File $path not exist by this way, please type correct relative path: ";
            $path = rtrim(fgets(STDIN));
        };
        if (mime_content_type($path) !== 'application/xml') {
            exit('Incorrect file format!');
        }

        echo "Please, input low limit of ages range (in years): ";
        fscanf(STDIN, "%d\n", $lowAge);

        $humanAge = range(0, 150);
        while (!in_array($lowAge, $humanAge)) {
            echo "Out of range of human life! Please try again: ";
            fscanf(STDIN, "%d\n", $lowAge);
        }

        echo "Please, input high limit of ages range (in years too): ";
        fscanf(STDIN, "%d\n", $highAge);
        while ($highAge < $lowAge) {
            echo "Type more then $lowAge: ";
            fscanf(STDIN, "%d\n", $highAge);
            while (!in_array($highAge, $humanAge)) {
                echo "Out of range of human life! Please try again: ";
                fscanf(STDIN, "%d\n", $highAge);
            }
        }
        echo "Do you confirm this data?\n\tInput file: $path\n\tAge from $lowAge to $highAge\nType Yes or No: ";
        $confirm = rtrim(fgets(STDIN));
        while (!in_array($confirm, array('n', 'no', 'yes', 'y'))) {
            echo "Incorrect answer, type Yes or No: ";
            $confirm = rtrim(fgets(STDIN));
        }

    } while ($confirm == 'n' || $confirm == 'no');

    $newFile = new FilterXML($path, $lowAge, $highAge);
    $newFile->run();
}

echo 'Peak memory usage: ' . formatBytes(memory_get_peak_usage()) . "\n";
