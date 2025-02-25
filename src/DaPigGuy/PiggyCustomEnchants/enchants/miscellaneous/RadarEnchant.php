<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class RadarEnchant extends TickingEnchantment
{
    use ToggleTrait;

    public string $name = "Radar";

    public int $usageType = CustomEnchant::TYPE_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_COMPASS;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 50];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $detected = $this->findNearestPlayer($player, $level * $this->extraData["radiusMultiplier"]);
        $this->setCompassPosition($player, $detected->getPosition() ?? $player->getWorld()->getSafeSpawn());
        if ($item->equalsExact($player->getInventory()->getItemInHand())) {
            if (is_null($detected)) {
                $player->sendTip(TextFormat::RED . "No players found.");
            } else {
                $player->sendTip(TextFormat::GREEN . "Nearest player " . round($player->getPosition()->distance($detected->getPosition()), 1) . " blocks away.");
            }
        }
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && $player->isOnline()) $this->setCompassPosition($player, $player->getWorld()->getSafeSpawn());
    }

    public function setCompassPosition(Player $player, Position $position): void
    {
        $pk = new SetSpawnPositionPacket();
        $pk->x = $pk->x2 = $position->getFloorX();
        $pk->y = $pk->y2 = $position->getFloorY();
        $pk->z = $pk->z2 = $position->getFloorZ();
        $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
        $pk->dimension = DimensionIds::OVERWORLD;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function findNearestPlayer(Player $player, int $range): ?Player
    {
        $nearestPlayer = null;
        $nearestPlayerDistance = $range;
        foreach ($player->getWorld()->getPlayers() as $p) {
            $distance = $player->getPosition()->distance($p->getPosition());
            if ($distance <= $range && $distance < $nearestPlayerDistance && $player !== $p && $p->isAlive() && !$p->isClosed() && !$p->isFlaggedForDespawn()) {
                $nearestPlayer = $p;
                $nearestPlayerDistance = $distance;
            }
        }
        return $nearestPlayer;
    }
}