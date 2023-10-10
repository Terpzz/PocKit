<?php

namespace LemoniqPvP\PocKit\utils;

use dktapps\pmforms\ModalForm;
use LemoniqPvP\PocKit\Main;
use muqsit\invmenu\InvMenu;
use pocketmine\inventory\Inventory;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Item;
use pocketmine\player\Player;

class KitInventoryEditor {

    public static function openEditor(Player $player, string $kitId) {
        $config = Main::$instance->kits;
        $kits = $config->get("kits", null);
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $kit = $kits[$kitId];

        $items = [];
        foreach ($kit["items"] as $itemString) {
            $item = StringToItemParser::parse($itemString, true, true);
            if ($item !== null) {
                $items[] = $item;
            } else {
                $player->sendMessage("Failed to load an item from the kit. Please check the kit configuration.");
            }
        }

        $menu->getInventory()->setContents($items);
        $menu->setName("Kit " . $kitId);

        $menu->setInventoryCloseListener(function(Player $player, Inventory $inventory) use ($kitId, $kit) {
            $form = new ModalForm("Kit " . $kitId, "Do you want to confirm your changes?", function(Player $player, bool $choice) use ($kitId, $kit, $inventory): void {
                if ($choice) {
                    $kit["items"] = array_map(function(Item $item) {
                        return StringToItemParser::itemToString($item);
                    }, $inventory->getContents());
                    ConfigUtils::updateKit($kitId, $kit);
                }
                PresetForms::kitEditSelection($player, $kitId);
                return;
            });
            $player->sendForm($form);
        });

        $menu->send($player);
    }

}
