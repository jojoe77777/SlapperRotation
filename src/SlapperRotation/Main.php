<?php

namespace SlapperRotation;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\MoveEntityAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

	public function onEnable() {
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerMove(PlayerMoveEvent $ev) {
		$player = $ev->getPlayer();
		$from = $ev->getFrom();
		$to = $ev->getTo();
		if($from->distance($to) < 0.1) {
			return;
		}
		$maxDistance = $this->getConfig()->get("max-distance");
		foreach ($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player) as $e) {
			if($e instanceof Player) {
				continue;
			}
			if(substr($e->getSaveId(), 0, 7) !== "Slapper") {
				continue;
			}
			switch ($e->getSaveId()) {
				case "SlapperFallingSand":
				case "SlapperMinecart":
				case "SlapperBoat":
				case "SlapperPrimedTNT":
				case "SlapperShulker":
					continue 2;
			}
			$xdiff = $player->x - $e->x;
			$zdiff = $player->z - $e->z;
			$angle = atan2($zdiff, $xdiff);
			$yaw = (($angle * 180) / M_PI) - 90;
			$ydiff = $player->y - $e->y;
			$v = new Vector2($e->x, $e->z);
			$dist = $v->distance($player->x, $player->z);
			$angle = atan2($dist, $ydiff);
			$pitch = (($angle * 180) / M_PI) - 90;

			if($e->getSaveId() === "SlapperHuman") {
				$pk = new MovePlayerPacket();
				$pk->entityRuntimeId = $e->getId();
				$pk->position = $e->asVector3()->add(0, $e->getEyeHeight(), 0);
				$pk->yaw = $yaw;
				$pk->pitch = $pitch;
				$pk->headYaw = $yaw;
				$pk->onGround = $e->onGround;
			} else {
				$pk = new MoveEntityAbsolutePacket();
				$pk->entityRuntimeId = $e->getId();
				$pk->position = $e->asVector3();
				$pk->yaw = $yaw;
				$pk->headYaw = $yaw;
				$pk->pitch = $pitch;
			}
			$player->dataPacket($pk);
		}
	}

}
