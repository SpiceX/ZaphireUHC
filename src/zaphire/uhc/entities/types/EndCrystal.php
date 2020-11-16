<?php

namespace zaphire\uhc\entities\types;

use zaphire\uhc\arena\Arena;
use zaphire\uhc\ZaphireUHC;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class EndCrystal extends Entity
{

    public const TAG_SHOW_BOTTOM = "ShowBottom";

    public const NETWORK_ID = self::ENDER_CRYSTAL;

    public $height = 0.98;
    public $width = 0.98;
    /** @var int */
    private $radius;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        $this->radius = 0;
        parent::__construct($level, $nbt);
    }

    public function initEntity(): void
    {
        if (!$this->namedtag->hasTag(self::TAG_SHOW_BOTTOM, ByteTag::class)) {
            $this->namedtag->setByte(self::TAG_SHOW_BOTTOM, 0);
        }
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTagVisible(true);
        $this->radius = 0;
        parent::initEntity();
    }

    public function isShowingBottom(): bool
    {
        return boolval($this->namedtag->getByte(self::TAG_SHOW_BOTTOM));
    }

    /**
     * @param bool $value
     */
    public function setShowingBottom(bool $value)
    {
        $this->namedtag->setByte(self::TAG_SHOW_BOTTOM, intval($value));
    }

    /**
     * @param Vector3 $pos
     */
    public function setBeamTarget(Vector3 $pos)
    {
        $this->namedtag->setTag(new ListTag("BeamTarget", [new DoubleTag("", $pos->getX()), new DoubleTag("", $pos->getY()), new DoubleTag("", $pos->getZ())]));
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
            $source->setCancelled(true);
            $player = $source->getDamager();
            if ($player instanceof Player) {
                if (!ZaphireUHC::getInstance()->getSqliteProvider()->verifyPlayerInDB($player)) {
                    ZaphireUHC::getInstance()->getSqliteProvider()->addPlayer($player);
                }
                if (ZaphireUHC::getInstance()->getJoinGameQueue()->inQueue($player)) {
                    $player->sendMessage("§c§l» §r§7You are in a queue");
                    return;
                }
                /** @var Arena $arena */
                $arena = ZaphireUHC::getInstance()->getArenaManager()->getAvailableArena();
                if ($arena !== null) {
                    ZaphireUHC::getInstance()->getJoinGameQueue()->joinToQueue($player, $arena);
                } else {
                    ZaphireUHC::getInstance()->getFormManager()->sendAvailableArenaNotFound($player);
                }
                return;
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $size = 1.2;
        $x = $this->getX();
        $y = $this->getY();
        $z = $this->getZ();
        $a = cos(deg2rad($this->radius / 0.09)) * $size;
        $b = sin(deg2rad($this->radius / 0.09)) * $size;
        $c = cos(deg2rad($this->radius / 0.3)) * $size;
        $this->getLevel()->addParticle(new GenericParticle(new Vector3($x - $a, $y + $c + 1.4, $z - $b), Particle::TYPE_COLORED_FLAME));
        $this->getLevel()->addParticle(new GenericParticle(new Vector3($x + $a, $y + $c + 1.4, $z + $b), Particle::TYPE_COLORED_FLAME));
        $this->radius++;
        $availableArenas = (ZaphireUHC::getInstance()->getArenaManager()->getAvailableArena() !== null) ? "§aClick to Join" : "§cRunning Game";
        $playing = ZaphireUHC::getInstance()->getArenaManager()->getTotalPlaying();
        $spectating = ZaphireUHC::getInstance()->getArenaManager()->getTotalSpectating();
        $this->setNameTag("§l§cZaphire UHC\n§r" . $availableArenas . "\n" .
            "§fPlaying: §7" . $playing . "\n" .
            "§fSpectating: §7" . $spectating
        );
        return parent::entityBaseTick($tickDiff);
    }
}
