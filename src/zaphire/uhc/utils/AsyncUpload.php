<?php


namespace zaphire\uhc\utils;

use pocketmine\scheduler\AsyncTask;
use zaphire\uhc\lib\Kraken;

class AsyncUpload extends AsyncTask
{

    /** @var string */
    private $filePath;
    /** @var string */
    private $name;
    /** @var Kraken */
    private $kraken;

    /**
     * AsyncUpload constructor.
     * @param string $filePath
     * @param string $name
     */
    public function __construct(string $filePath, string $name)
    {
        $this->filePath = $filePath;
        $this->name = $name;
        $this->kraken = new Kraken("0c3ecbf8a3458d932e86cc34bb5f8c4e", "d86ea5519637b64661819d661462888af3def9ec");
    }

    public function onRun()
    {
        $params = array(
            "file" => $this->filePath,
            "wait" => true
        );

        $data = $this->kraken->upload($params);
        if ($data["success"]) {
            $urlFile = @fopen(str_replace('.png', '.url', $this->filePath), 'wb+');
            @fwrite($urlFile, $data["kraked_url"]);
            @fclose($urlFile);
        }
    }

    public function __destruct()
    {
        unset($this->kraken);
    }

}