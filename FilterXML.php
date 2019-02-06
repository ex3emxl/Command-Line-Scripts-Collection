<?php
/**
 * Filter XML file and generate new by specific data
 *
 * @author Se Mi <ex3emxl@gmail.com>
 * @version 1.0
 *
 */

/**
 * Class used to filter XML and generate new XML file
 *
 * @since 1.0.0
 */
class FilterXML
{

    /**
     * Path for source file
     *
     * @var string
     */
    private $path;

    /**
     * Filter criteria.
     * Low limit of Age range
     *
     * @var integer
     */
    private $lowAge = 0;

    /**
     * Filter criteria.
     * High limit of Age range
     *
     * @var integer
     */
    private $highAge = 0;

    /**
     * FilterXML constructor.
     *
     * @param array $params
     */
    function __construct($params)
    {
        if (
            is_array($params)
            && array_key_exists('path', $params)
            && array_key_exists('lowAge', $params)
            && array_key_exists('highAge', $params)
        ) {
            $this->path = $params['path'];
            $this->lowAge = $params['lowAge'];
            $this->highAge = $params['highAge'];
        }
    }

    /**
     * Filter XML and generate new XML file by specify criteria.
     *
     * @param int Filter criteria. Low limit of Age range.
     * @param int Filter criteria. High limit of Age range.
     * @param string The relative path of the source file.
     *
     * @return Generator
     */
    private function filterFile($path, $lowAge, $highAge)
    {
        $srcXML = fopen($path, "r");
        $targetXML = fopen('target.xml', "wb");
        $marker = 0;
        $node = "<?xml version='1.0' encoding='utf-8'?>\n<Users>\n";

        fwrite($targetXML, $node);
        try {
            while (!feof($srcXML)) {
                $lineXML = fgets($srcXML);
                if (preg_match("/\<user\>/", $lineXML)) {
                    $marker = 1;
                }
                if ($marker == 1) {
                    preg_match_all("/\<age\>(.*?)\<\/age\>/s", $lineXML, $matches);
                    $node .= $lineXML;
                    if (isset($matches[1][0]) && ($matches[1][0] > $highAge || $matches[1][0] < $lowAge)) {
                        $node = '';
                        $marker = 0;
                        yield;
                    }
                }
                if (preg_match("/\<\/user>/", $lineXML)) {
                    fwrite($targetXML, $node);
                    $marker = 0;
                    $node = '';
                    yield;
                }
            }
        } finally {
            $node .= "</Users>";
            fwrite($targetXML, $node);
            fclose($targetXML);
            fclose($srcXML);
        }
    }

    /**
     * Formatting bytes
     *
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array("b", "kb", "mb", "gb", "tb");

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . " " . $units[$pow];
    }


    /**
     * Runs filter and generation target file.
     */
    public function run()
    {
        $start = microtime(true);

        // for high speed we not be show any data like progress bar
        foreach ($this->filterFile($this->path, $this->lowAge, $this->highAge) as $line) {
        }
        echo 'Time of script execution: ' . round(microtime(true) - $start, 4) . ' sec.' . "\n";
        echo 'Peak memory usage: ' . self::formatBytes(memory_get_peak_usage()) . "\n";
    }

}



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
     * @var array['path']        string Path for source file.
     *           ['lowAge']      int Low limit of Age range.
     *           ['width']       int High limit of Age range.
     */
    private $collection = array(
        'path' => '',
        'lowAge' => '',
        'width' => '',
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

        $this->collection = array(
            'path' => $path,
            'lowAge' => $lowAge,
            'highAge' => $highAge
        );
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

}

$collectionData = new CollectDataFromCommandLine();

$targetFile = new FilterXML($collectionData->collection);

$targetFile->run();
