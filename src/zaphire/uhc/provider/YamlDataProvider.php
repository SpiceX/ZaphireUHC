<?php

/**
 * Copyright 2020-2022 ZaphireUHC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);
namespace zaphire\uhc\provider;

use zaphire\uhc\ZaphireUHC;


class YamlDataProvider extends Provider
{

	/** @var ZaphireUHC $plugin */
	private $plugin;
	/** @var int */
    private $maxArenas;
    /** @var int */
    private $maxScenarios;
    /** @var bool */
    private $enabledDiscord;
    /** @var bool */
    private $enabledKraken;
    /** @var string|null */
    private $discordWebhook;
    /** @var string|null */
    private $krakenKey;
    /** @var string|null */
    private $krakenSecret;
    /**
     * @var mixed|null
     */
    private $gameTimeEvent;

    /**
	 * YamlDataProvider constructor.
	 * @param ZaphireUHC $plugin
	 */
	public function __construct(ZaphireUHC $plugin)
	{
		$this->plugin = $plugin;
		$this->init();
	}

	public function init()
	{
		if (!is_dir($this->getDataFolder())) {
			@mkdir($this->getDataFolder());
		}
		if (!is_dir($this->getDataFolder() . "arenas")) {
			@mkdir($this->getDataFolder() . "arenas");
		}
		if (!is_dir($this->getDataFolder() . "saves")) {
			@mkdir($this->getDataFolder() . "saves");
		}
        if (!is_dir($this->getDataFolder() . "heads")) {
            @mkdir($this->getDataFolder() . "heads");
        }
        if (!is_dir($this->getDataFolder() . "skins")) {
            @mkdir($this->getDataFolder() . "skins");
        }
		$this->maxArenas = (int)$this->plugin->getConfig()->get('max_arenas', 1);
		$this->maxScenarios = (int)$this->plugin->getConfig()->get('max_scenarios', 2);
		$this->enabledDiscord = (bool)$this->plugin->getConfig()->get('enable_discord', false);
		$this->enabledKraken = (bool)$this->plugin->getConfig()->get('enable_kraken', true);
		$this->discordWebhook = $this->plugin->getConfig()->getNested('discord_connector');
		$this->krakenKey = $this->plugin->getConfig()->getNested('kraken_key');
		$this->krakenSecret = $this->plugin->getConfig()->getNested('kraken_secret');
		$this->gameTimeEvent = $this->plugin->getConfig()->getNested('uhc_event_time', "1 hour");
	}

	public function getMaxArenas(){
	    return $this->maxArenas;
    }

    /**
     * @return string
     */
    public function getHeadsFolder(): string
    {
	    return $this->plugin->getDataFolder() . 'heads' . DIRECTORY_SEPARATOR;
    }

	/**
	 * @return string $dataFolder
	 */
	private function getDataFolder(): string
	{
		return $this->plugin->getDataFolder();
	}

    /**
     * @return int
     */
    public function getMaxScenarios(): int
    {
        return $this->maxScenarios;
    }

    /**
     * @return bool
     */
    public function isEnabledDiscord(): bool
    {
        return $this->enabledDiscord;
    }

    /**
     * @return bool
     */
    public function isEnabledKraken(): bool
    {
        return $this->enabledKraken;
    }

    /**
     * @return string|null
     */
    public function getDiscordWebhook(): ?string
    {
        return $this->discordWebhook;
    }

    /**
     * @return string|null
     */
    public function getKrakenKey(): ?string
    {
        return $this->krakenKey;
    }

    /**
     * @return string|null
     */
    public function getKrakenSecret(): ?string
    {
        return $this->krakenSecret;
    }

    /**
     * @return string
     */
    public function getGameTimeEvent(): string
    {
        return $this->gameTimeEvent;
    }
}
