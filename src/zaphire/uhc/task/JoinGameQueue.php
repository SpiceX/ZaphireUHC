<?php

namespace zaphire\uhc\task;

use Exception;
use zaphire\uhc\arena\Arena;
use zaphire\uhc\ZaphireUHC;
use zaphire\uhc\utils\Scoreboard;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class JoinGameQueue extends Task
{
    /** @var array */
    public $arenas = [];
    /** @var Scoreboard[] */
    public $scoreboards = [];
    /** @var ZaphireUHC */
    private $plugin;
    /** @var array */
    public $startingTimes = [];

    /**
     * JoinGameQueue constructor.
     * @param ZaphireUHC $plugin
     */
    public function __construct(ZaphireUHC $plugin)
    {
        $this->plugin = $plugin;
    }


    /**
     * @param int $currentTick
     * @throws Exception
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->arenas as $arena => $players) {
            $arena = $this->plugin->getArenaManager()->getArena($arena);
            if (count($players) < 10) {
                $this->updateScoreboards([
                    1 => '§7------------------',
                    2 => ' §cWaiting players...',
                    3 => " §cMap: §7{$arena->level->getFolderName()}",
                    4 => " §cInQueue: §7(" . count($this->arenas[$arena->level->getFolderName()]) . '/' . (int)$arena->data['slots'] . ')',
                    5 => ' §7-----------------',
                ]);
            } else {
                $this->updateScoreboards([
                    1 => '§7------------------',
                    2 => ' §cStarting game in: ' . $this->startingTimes[$arena->level->getFolderName()],
                    3 => " §cMap: §7{$arena->level->getFolderName()}",
                    4 => " §cInQueue: §7(" . count($this->arenas[$arena->level->getFolderName()]) . '/' . (int)$arena->data['slots'] . ')',
                    5 => ' §7-----------------',
                ]);
                if ($this->startingTimes[$arena->level->getFolderName()] === 0) {
                    foreach ($players as $player) {
                        $arena->joinToArena($player);
                        $this->leaveQueue($player);
                    }
                    $arena->startGame();
                    $this->arenas[$arena->level->getFolderName()] = [];
                    $this->startingTimes[$arena->level->getFolderName()] = 10;
                }
                $this->startingTimes[$arena->level->getFolderName()]--;
            }

        }
    }

    public function getArenaByPlayer(Player $player)
    {
        foreach ($this->arenas as $arena => $players) {
            foreach ($players as $p) {
                if ($player->getName() === $p->getName()) {
                    return $this->plugin->getArenaManager()->getArena($arena);
                }
            }
        }
        return null;
    }

    /**
     * @param Player $player
     * @param Arena $arena
     */
    public function joinToQueue(Player $player, Arena $arena)
    {
        $this->arenas[$arena->level->getFolderName()][] = $player;
        $this->scoreboards[$player->getName()] = $scoreboard = new Scoreboard($player);
        $scoreboard->spawn("§l§cZaphire§fUHC");
        $player->getInventory()->setItem(0, Item::get(Item::ENCHANTED_BOOK)->setCustomName('§aVote'));
        $player->getInventory()->setItem(8, Item::get(Item::REDSTONE)->setCustomName('§cLeave Queue'));
        $player->sendMessage("§l§c» §r§7You have joined a queue for UHC.");
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function inQueue(Player $player){
        foreach ($this->arenas as $arena => $players) {
            foreach ($players as $index => $wantedPlayer) {
                /** @var Player $wantedPlayer */
                if ($player->getId() === $wantedPlayer->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param Player $player
     */
    public function leaveQueue(Player $player)
    {
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        foreach ($this->arenas as $arena => $players) {
            foreach ($players as $index => $wantedPlayer) {
                /** @var Player $wantedPlayer */
                if ($player->getId() === $wantedPlayer->getId()) {
                    unset($this->arenas[$arena][$index]);
                    $arena = $this->plugin->getArenaManager()->getArena($arena);
                    $scenario = $arena->voteManager->getScenarioVoted($player);
                    if ($scenario !== null) {
                        $arena->voteManager->reduceVote($player, $scenario);
                    }
                }
            }
        }
        if (isset($this->scoreboards[$player->getName()])) {
            $this->scoreboards[$player->getName()]->despawn();
            unset($this->scoreboards[$player->getName()]);
        }
        $player->sendMessage("§a§l» §r§7You have left the queue.");
    }

    /**
     * @param array $lines
     * @throws Exception
     */
    private function updateScoreboards(array $lines)
    {
        foreach ($this->scoreboards as $scoreboard) {
            $scoreboard->removeLines();
            foreach ($lines as $index => $line) {
                $scoreboard->setScoreLine($index, $line);
            }
        }
    }
}