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

namespace zaphire\uhc\forms;

use zaphire\uhc\arena\Arena;
use zaphire\uhc\arena\ArenaManager;
use zaphire\uhc\arena\scenario\Scenarios;
use zaphire\uhc\forms\elements\Button;
use zaphire\uhc\forms\elements\Image;
use zaphire\uhc\forms\types\MenuForm;
use zaphire\uhc\ZaphireUHC;
use pocketmine\Player;

class FormManager
{

    /**
     * @var ZaphireUHC
     */
    private $plugin;

    public function __construct(ZaphireUHC $plugin)
    {
        $this->plugin = $plugin;
    }

    public function sendSpectateArenasForm(Player $player)
    {
        $player->sendForm(new MenuForm("§l§b» §l§cZaphire§fUHC §b«", "§7Select an arena to spectate: ",
            $this->getArenasButtons(), function (Player $player, Button $selected): void {
                /** @var Arena $arena */
                $arena = $this->getArenaManager()->getArena(explode("\n", $selected->getText())[0]);
                $arena->spectateToArena($player);
            }));
    }

    public function sendAvailableArenaNotFound(Player $player)
    {
        $form = new MenuForm("§l§b» §l§cZaphire§fUHC §b«", "§fThe are not available uhc arenas, would you like spectate a game?",
            [
                new Button("§aSee games", new Image("https://vignette.wikia.nocookie.net/mineplex/images/e/e2/UHClogo.png", Image::TYPE_URL)),
                new Button("§cExit", new Image("http://clipart-library.com/images/M8cGMGKTa.png", Image::TYPE_URL))
            ], function (Player $player, Button $selected): void {
                if ($selected->getValue() === 0) {
                    $this->sendSpectateArenasForm($player);
                }
            });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     */
    public function sendVoteForm(Player $player)
    {
        $player->sendForm(new MenuForm("§l§b» §l§cZaphire§fUHC §b«", "§7Select your favorite scenario:",
            $this->getScenarioButtons(), function (Player $player, Button $selected): void {
                $arena = $this->plugin->getJoinGameQueue()->getArenaByPlayer($player);
                if ($arena !== null) {
                    if ($arena->voteManager->hasVoted($player)) {
                        $player->sendMessage("§l§c» §r§7You have already voted.");
                        return;
                    }
                    $arena->voteManager->addVote($player, $selected->getText());
                    $player->sendMessage("§l§a» §r§7You have voted for §f" . $selected->getText());
                }
            }));
    }

    /**
     * @param Player $player
     * @param array $players
     */
    public function sendSpectatePlayer(Player $player, array $players)
    {
        $form = new MenuForm("§l§cZaphire§fUHC", "§7Select a player:", $this->playersArrayToButtons($players),
            function (Player $player, Button $selected) use ($players): void {
                if (!is_array($players) || empty($players)) {
                    return;
                }
                /** @var Player[] $players */
                $player->teleport($players[$selected->getText()]->asPosition());
                $player->sendMessage("§l§a» §r§7Now Spectating " . $players[$selected->getText()]->getName());
            });
        $player->sendForm($form);
    }

    public function getArenasButtons()
    {
        $buttons = [];
        foreach ($this->getArenaManager()->getArenas() as $name => $arena) {
            $buttons[] = new Button($name . "\n§aP: " . count($arena->players) . "  §cS: " . count($arena->spectators), new Image("https://www.clker.com/cliparts/N/C/J/i/O/V/golden-apple-hi.png", Image::TYPE_URL));
        }
        return $buttons;
    }

    private function playersArrayToButtons(array $players)
    {
        $array = [];
        if ($this->plugin->getDataProvider()->isEnabledKraken()){
            foreach ($players as $player) {
                /** @var Player $player */
                $file = @fopen($this->plugin->getDataProvider()->getHeadsFolder() . $player->getName() . '.url', 'r');
                $array[] = new Button($player->getName(), new Image(fread($file, filesize($this->plugin->getDataProvider()->getHeadsFolder() . $player->getName() . '.url'))));
            }
        } else {
            foreach ($players as $player) {
                /** @var Player $player */
                $array[] = new Button($player->getName());
            }
        }

        return $array;
    }

    public function getScenarioButtons()
    {
        $buttons = [];
        foreach (Scenarios::getScenarios() as $scenario) {
            $buttons[] = new Button($scenario);
        }
        return $buttons;
    }

    /**
     * @return ArenaManager
     */
    private function getArenaManager(): ArenaManager
    {
        return $this->plugin->getArenaManager();
    }
}