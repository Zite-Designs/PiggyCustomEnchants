<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class AttackerDeterrentEnchant extends ReactiveEnchantment
{
    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    /** @var Effect[] */
    private $effects;
    /** @var array */
    private $durationMultiplier;
    /** @var array */
    private $amplifierMultiplier;

    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, array $effects, array $durationMultiplier, array $amplifierMultiplier, int $rarity = Rarity::RARE)
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->effects = $effects;
        $this->durationMultiplier = $durationMultiplier;
        $this->amplifierMultiplier = $amplifierMultiplier;
        parent::__construct($plugin, $id);
    }

    public function getDefaultExtraData(): array
    {
        return ["durationMultipliers" => $this->durationMultiplier, "amplifierMultipliers" => $this->amplifierMultiplier];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Living) {
                foreach ($this->effects as $key => $effect) {
                    $damager->getEffects()->add(new EffectInstance($effect, $this->extraData["durationMultipliers"][$key] * $level, $this->extraData["amplifierMultipliers"][$key] * $level));
                }
            }
        }
    }
}