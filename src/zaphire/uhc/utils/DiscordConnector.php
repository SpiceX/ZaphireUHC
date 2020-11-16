<?php


namespace zaphire\uhc\utils;


use JsonException;
use pocketmine\scheduler\AsyncTask;

class DiscordConnector extends AsyncTask
{
    /** @var string */
    private $webHookID;
    /** @var string */
    private $message;

    public function __construct(string $webHookID, string $message)
    {
        $this->webHookID = $webHookID;
        $this->message = $message;
    }

    public function onRun(): void
    {
        $curl = curl_init();

        try {
            $data_string = json_encode(array(
                'content' => $this->message,
                'username' => null,
                'avatar_url' => null,
                'file' => null,
                'embeds' => null
            ), JSON_THROW_ON_ERROR, 512);
            curl_setopt($curl, CURLOPT_URL, 'https://discordapp.com/Base/webhooks/' . $this->webHookID);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data_string)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_exec($curl);
            curl_close($curl);
        } catch (JsonException $e) {
            print_r($e->getTrace());
        }
    }
}