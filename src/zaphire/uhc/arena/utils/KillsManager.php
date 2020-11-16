<?php

namespace zaphire\uhc\arena\utils;

use zaphire\uhc\arena\Arena;
use pocketmine\Player;

class KillsManager
{
    /** @var Arena */
    private $arena;
    /** @var Integer[] */
    private $kills = [];

    /**
     * VoteManager constructor.
     * @param Arena $arena
     */
    public function __construct(Arena $arena)
    {
        $this->arena = $arena;
    }

    /**
     * @param Player $player
     */
    public function addKill(Player $player): void
    {
        if (!isset($this->kills[$player->getName()])) {
            $this->kills[$player->getName()] = 1;
            return;
        }
        $this->kills[$player->getName()]++;
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getKills(Player $player): int
    {
        return $this->kills[$player->getName()] ?? 0;
    }

    public function getTopKills(){
        $mostKills = [];
        $string = '';
        arsort($this->kills);
        $scenarios = array_keys($this->kills);
        for ($i = 0; $i < 3; $i++) {
            $selectedScenarios[] = $scenarios[$i] ?? '';
        }
        foreach ($mostKills as $mostKill => $kills) {
            $string .= "§4- §c$mostKill  §4Kills: §f" . $this->kills[$mostKill] ?? 5 . "\n";
        }
        return $string;
    }

    public function reload()
    {
        $this->kills = [];
    }
}