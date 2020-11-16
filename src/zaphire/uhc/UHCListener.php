<?php

namespace zaphire\uhc;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use zaphire\uhc\utils\AsyncUpload;

class UHCListener implements Listener
{

    /** @var ZaphireUHC */
    private $plugin;

    /**
     * UHCListener constructor.
     * @param ZaphireUHC $plugin
     */
    public function __construct(ZaphireUHC $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $this->plugin->getPluginUtils()->removeHead($player);
        $this->plugin->getJoinGameQueue()->leaveQueue($player);
    }

    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $arena = $this->plugin->getJoinGameQueue()->getArenaByPlayer($player);
        if ($arena !== null) {
            switch ($item->getId()) {
                case Item::REDSTONE:
                    if ($item->getCustomName() === '§cLeave Queue') {
                        $this->plugin->getJoinGameQueue()->leaveQueue($player);
                    }
                    break;
                case Item::ENCHANTED_BOOK:
                    if ($item->getCustomName() === '§aVote') {
                        $this->plugin->getFormManager()->sendVoteForm($player);
                    }
                    break;
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $this->plugin->getPluginUtils()->savePlayerHead($player);
        $this->plugin->getServer()->getAsyncPool()->submitTask(
            new AsyncUpload(realpath($this->plugin->getDataFolder() . 'heads/' . $player->getName() . '.png'), $player->getName() . '.png')
        );
    }

    /**
     * @param PlayerDropItemEvent $event
     */
    public function onPlayerDropItem(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();
        if ($this->plugin->getJoinGameQueue()->inQueue($player)) {
            $event->setCancelled();
        }
    }
}