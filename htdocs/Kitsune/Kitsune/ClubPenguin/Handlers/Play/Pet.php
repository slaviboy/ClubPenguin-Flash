<?php

namespace Kitsune\ClubPenguin\Handlers\Play;

use Kitsune\Logging\Logger;
use Kitsune\ClubPenguin\Packets\Packet;

//TODO: Implement AS2 puffle system
trait Pet {

	public $puffles = array();
	public $treats = array(1, 2);

	protected function joinPuffleData($puffleData, $iglooAppend = false) {
		$puffles = implode("%", array_map(
			function($puffle) use ($iglooAppend) {
				$puffleId = $puffle["ID"];

				if($iglooAppend !== false) {
					if(!isset($this->puffles[$puffleId])) {
						$this->puffles[$puffleId] = array(100, 100, 100, 0, 0, 0, 0);
					}
					$puffle = array_merge($puffle, $this->puffles[$puffleId]);
				}

				return implode('|', $puffle);
			}, $puffleData));

		return $puffles;
	}

	protected function handleSendAdoptPuffle($socket) {
		$penguin = $this->penguins[$socket];

		$puffleType = Packet::$Data[2];
		$puffleName = Packet::$Data[3];

		if(is_numeric($puffleType)) {
			if(!ctype_alpha($puffleName)) {
				return $penguin->send("%xt%e%-1%441%");
			}

			$puffleData = $penguin->database->getPlayerPuffles($penguin->id);
			if(count($puffleData) >= 20) {
				return $penguin->send("%xt%e%-1%440%");
			}

			if($penguin->coins < 800) {
				return $penguin->send("%xt%e%-1%401%");
			} else {
				$puffleId = $penguin->database->adoptPuffle($penguin->id, $puffleName, $puffleType);
				$adoptionDate = time();

				$puffleData = $penguin->database->getPlayerPuffle($puffleId);
				$puffle = $this->joinPuffleData($puffleData);

				$postcardId = $penguin->database->sendMail($penguin->id, "sys", 0, $puffleName, $adoptionDate, 111);
				$penguin->send("%xt%mr%-1%sys%0%111%$puffleName%$adoptionDate%$postcardId%");

				$penguin->setCoins($penguin->coins - 800);
				$penguin->send("%xt%pn%{$penguin->room->internalId}%{$penguin->coins}%$puffle%");
			}
		}

	}

	protected function handleGetPufflesByPlayerId($socket) {
		$penguin = $this->penguins[$socket];

		$playerId = Packet::$Data[2];

		if(is_numeric($playerId) && $penguin->database->playerIdExists($playerId)) {
			$puffleData = $penguin->database->getPlayerPuffles($playerId);

			$puffles = $this->joinPuffleData($puffleData, true);

			$penguin->send("%xt%pg%{$penguin->room->internalId}%$puffles%");
		}
	}

	protected function handleGetMyPlayerPuffles($socket) {
		$penguin = $this->penguins[$socket];

		$puffleData = $penguin->database->getPlayerPuffles($penguin->id);
		$puffles = $this->joinPuffleData($puffleData);

		$penguin->send("%xt%pgu%{$penguin->room->internalId}%$puffles%");
	}

	protected function handleSendPuffleWalk($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			$puffleData = $penguin->database->getPlayerPuffle($puffleId);

			list($maxHealth, $maxHunger, $maxRest, $x, $y, $unknown, $walking) = $this->puffles[$puffleId];
			$walking = ($walking == 1 ? 0 : 1);
			$this->puffles[$puffleId] = array($maxHealth, $maxHunger, $maxRest, $x, $y, $unknown, $walking);

			$puffle = $this->joinPuffleData($puffleData, true);

			$penguin->walkingPuffle = $puffleId;
			$penguin->room->send("%xt%pw%{$penguin->room->internalId}%{$penguin->id}%$puffle%");
		}
	}

	protected function handleSendPuffleMove($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];
		$puffleX = Packet::$Data[3];
		$puffleY = Packet::$Data[4];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			$puffleData = $penguin->database->getPlayerPuffle($puffleId);

			list($maxHealth, $maxHunger, $maxRest, $x, $y, $unknown, $walking) = $this->puffles[$puffleId];
			$x = $puffleX;
			$y = $puffleY;
			$this->puffles[$puffleId] = array($maxHealth, $maxHunger, $maxRest, $x, $y, $unknown, $walking);

			$puffle = $this->joinPuffleData($puffleData, true);

			$penguin->room->send("%xt%pm%{$penguin->room->internalId}%$puffleId%$puffleX%$puffleY%");
		}
	}

	protected function handleSendPuffleFeed($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			$puffleData = $penguin->database->getPlayerPuffle($puffleId);
			$puffle = $this->joinPuffleData($puffleData, true);

			$penguin->setCoins($penguin->coins - 10);

			$penguin->room->send("%xt%pf%{$penguin->room->internalId}%{$penguin->coins}%$puffle%");
		}
	}

	protected function handleSendPuffleTreat($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];
		$treatType = Packet::$Data[3];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			if(in_array($treatType, $this->treats)) {
				$puffleData = $penguin->database->getPlayerPuffle($puffleId);
				$puffle = $this->joinPuffleData($puffleData, true);

				$penguin->setCoins($penguin->coins - 5);

				$penguin->room->send("%xt%pt%{$penguin->room->internalId}%{$penguin->coins}%$puffle%$treatType%");
			}
		}
	}

	protected function handleSendPuffleRest($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			$puffleData = $penguin->database->getPlayerPuffle($puffleId);
			$puffle = $this->joinPuffleData($puffleData, true);

			$penguin->room->send("%xt%pr%{$penguin->room->internalId}%$puffle%");
		}
	}

	protected function handleSendPufflePlay($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			$puffleData = $penguin->database->getPlayerPuffle($puffleId);
			$puffle = $this->joinPuffleData($puffleData, true);

			$penguin->room->send("%xt%pp%{$penguin->room->internalId}%$puffle%1%");
		}
	}

	protected function handleSendPuffleBath($socket) {
		$penguin = $this->penguins[$socket];

		$puffleId = Packet::$Data[2];

		if(is_numeric($puffleId) && $penguin->database->ownsPuffle($puffleId, $penguin->id) && isset($this->puffles[$puffleId])) {
			$puffleData = $penguin->database->getPlayerPuffle($puffleId);
			$puffle = $this->joinPuffleData($puffleData, true);

			$penguin->setCoins($penguin->coins - 10);

			$penguin->room->send("%xt%pb%{$penguin->room->internalId}%{$penguin->coins}%$puffle%");
		}
	}

}

?>
