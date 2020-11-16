<?php

namespace zaphire\uhc\entities;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use zaphire\uhc\entities\types\Creeper;
use zaphire\uhc\entities\types\EndCrystal;
use zaphire\uhc\entities\types\Leaderboard;
use zaphire\uhc\utils\Fireworks;
use zaphire\uhc\utils\FireworksRocket;
use zaphire\uhc\ZaphireUHC;

class EntityManager
{
    /**
     * @var ZaphireUHC
     */
    private $plugin;

    public function __construct(ZaphireUHC $plugin)
    {
        $this->plugin = $plugin;
        $this->init();
    }

    public function init()
    {
        Entity::registerEntity(Leaderboard::class, true, ['Leaderboard']);
        Entity::registerEntity(Creeper::class, true, ['Creeper', 'minecraft:creeper']);
        Entity::registerEntity(EndCrystal::class, true, ['EnderCrystalUHC', 'minecraft:endercrystaluhc']);
        ItemFactory::registerItem(new Fireworks());
        if (!Entity::registerEntity(FireworksRocket::class, false, ["FireworksRocket"])) {
            $this->plugin->getLogger()->error("Failed to register FireworksRocket entity with savename 'FireworksRocket'");
        }
    }

}