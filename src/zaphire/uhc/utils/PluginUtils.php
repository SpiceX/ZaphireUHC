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

namespace zaphire\uhc\utils;


use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\Player;
use zaphire\uhc\ZaphireUHC;

class PluginUtils
{
    public const lineLength = 30;
    public const charWidth = 6;
    public const spaceChar = ' ';

    /** @var ZaphireUHC */
    private $plugin;
    /** @var string */
    private $headDataFolder;

    /**
     * PluginUtils constructor.
     * @param ZaphireUHC $plugin
     */
    public function __construct(ZaphireUHC $plugin)
    {
        $this->plugin = $plugin;
        $this->headDataFolder = $plugin->getDataFolder() . 'heads';
    }

    /**
     * @return Vector3
     */
    public static function getRandomVector(): Vector3
    {
        $x = rand() / getrandmax() * 2 - 1;
        $y = rand() / getrandmax() * 2 - 1;
        $z = rand() / getrandmax() * 2 - 1;
        $v = new Vector3($x, $y, $z);
        return $v->normalize();
    }

    /**
     * @param Player $player
     * @param Block $block
     * @return int
     */
    public static function destroyTree(Player $player, Block $block): int
    {
        $damage = 0;
        if ($block->getId() !== Block::WOOD) {
            return $damage;
        }
        $down = $block->getSide(Vector3::SIDE_DOWN);
        if ($down->getId() == Block::WOOD) {
            return $damage;
        }

        $level = $block->getLevel();

        $cX = $block->getX();
        $cY = $block->getY();
        $cZ = $block->getZ();

        for ($y = $cY + 1; $y < 128; ++$y) {
            if ($level->getBlockIdAt($cX, $y, $cZ) == Block::AIR) {
                break;
            }
            for ($x = $cX - 4; $x <= $cX + 4; ++$x) {
                for ($z = $cZ - 4; $z <= $cZ + 4; ++$z) {
                    $block = $level->getBlock(new Vector3($x, $y, $z));

                    if ($block->getId() !== Block::WOOD && $block->getId() !== Block::LEAVES) {
                        continue;
                    }

                    ++$damage;
                    if ($block->getId() === Block::WOOD) {
                        if ($player->getInventory()->canAddItem(Item::get(Item::WOOD))) {
                            $player->getInventory()->addItem(Item::get(Item::WOOD));
                        }
                    }

                    $level->setBlockIdAt($x, $y, $z, 0);
                    $level->setBlockDataAt($x, $y, $z, 0);
                }
            }
        }
        return $damage;
    }

    public static function assocArrayToScoreboard(array $array)
    {
        $string = '';
        foreach ($array as $item) {
            $string .= "§b- §7{$item}\n";
        }
        return $string;
    }

    /**
     * @param Player $player
     */
    public static function addLightningBolt(Player $player)
    {
        $pk = new AddActorPacket();
        $pk->entityRuntimeId = $pk->entityUniqueId = Entity::$entityCount++;
        $pk->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::LIGHTNING_BOLT];
        $pk->position = $player->asPosition();
        $pk->motion = $player->getMotion();
        $player->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     */
    public function removeHead(Player $player)
    {
        @unlink($this->headDataFolder . DIRECTORY_SEPARATOR . $player->getName() . '.png');
    }

    public function savePlayerSkin(Player $player, $height = 64, $width = 64)
    {
        $pixel_array = str_split(bin2hex($player->getSkin()->getSkinData()), 8);
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $position = count($pixel_array) - 1;
        while (!empty($pixel_array)) {
            $x = $position % $width;
            $y = ($position - $x) / $height;
            $walkable = str_split(array_pop($pixel_array), 2);
            $color = array_map(static function ($val) {
                return hexdec($val);
            }, $walkable);
            $alpha = array_pop($color);
            $alpha = ((~((int)$alpha)) & 0xff) >> 1;
            $color[] = $alpha;
            imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, ...$color));
            $position--;
        }
        @unlink($this->plugin->getDataFolder() . 'skins/' . $player->getName() . '.png');
        imagepng($image, $this->plugin->getDataFolder() . 'skins/' . $player->getName() . '.png');
        @imagedestroy($image);
    }

    public function deleteFiles($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK);

            foreach ($files as $file) {
                $this->deleteFiles($file);
            }

            rmdir($target);
        } elseif (is_file($target)) {
            unlink($target);
        }
    }

    public function savePlayerHead(Player $player)
    {
        $this->savePlayerSkin($player);
        $path = $this->plugin->getDataFolder() . 'skins/' . $player->getName() . '.png';
        $image = imagecreatefrompng($path);
        $objective = $this->createHeadImage(8, 8);
        for ($y = 8; $y < 16; ++$y) {
            for ($x = 8; $x < 16; ++$x) {
                imagesetpixel($objective, $x - 8, $y - 8, imagecolorat($image, $x, $y));
            }
        }
        for ($y = 7; $y < 15; ++$y) {
            for ($x = 40; $x < 48; ++$x) {
                $color = imagecolorat($image, $x, $y);
                $index = imagecolorsforindex($image, $color);
                if ($index["alpha"] === 127) {
                    continue;
                }
                imagesetpixel($objective, $x - 40, $y - 8, $color);
            }
        }
        imagedestroy($image);
        $final = $this->createHeadImage(330, 360);
        imagecopyresized($final, $objective, 0, 0, 0, 0, imagesx($final), imagesy($final), imagesx($objective), imagesy($objective));
        imagedestroy($objective);
        @unlink($this->plugin->getDataFolder() . 'skins/' . $player->getName() . '.png');
        imagepng($final, $this->plugin->getDataFolder() . 'heads/' . $player->getName() . '.png');
        imagedestroy($final);
    }

    private function createHeadImage($width, $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);
        imagealphablending($image, false);
        $fill = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $fill);
        return $image;
    }

    public static function getCompassDirection(float $deg): string
    {
        //https://github.com/Muirfield/pocketmine-plugins/blob/master/GrabBag/src/aliuly/common/ExpandVars.php
        //Determine bearing in degrees
        $deg %= 360;
        if ($deg < 0) {
            $deg += 360;
        }

        if (22.5 <= $deg and $deg < 67.5) {
            return "Northwest";
        } elseif (67.5 <= $deg and $deg < 112.5) {
            return "North";
        } elseif (112.5 <= $deg and $deg < 157.5) {
            return "Northeast";
        } elseif (157.5 <= $deg and $deg < 202.5) {
            return "East";
        } elseif (202.5 <= $deg and $deg < 247.5) {
            return "Southeast";
        } elseif (247.5 <= $deg and $deg < 292.5) {
            return "South";
        } elseif (292.5 <= $deg and $deg < 337.5) {
            return "Southwest";
        } else {
            return "West";
        }
    }
}