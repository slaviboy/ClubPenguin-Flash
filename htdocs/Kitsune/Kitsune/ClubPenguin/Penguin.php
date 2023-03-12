<?php

namespace Kitsune\ClubPenguin;

use Kitsune;
use Kitsune\Logging\Logger;

class Penguin {

	public $id;
	public $username;

	public $identified = false;
	public $randomKey;

	public $color, $head, $face, $neck, $body, $hand, $feet, $photo, $flag;
	public $age;

	public $coins;

	public $inventory = array();

	public $moderator;
	public $muted = false;

	public $membershipDays;

	public $loginTime;
	public $minutesPlayed;

	public $activeIgloo;

	public $furniture = array();
	public $floors = array();
	public $igloos = array();
	public $recentStamps = "";

	public $buddies = array();
	public $buddyRequests = array();

	public $ignore = array();

	public $x = 0;
	public $y = 0;
	public $frame;

	public $tableId = null;
	public $waddleId = null;

	public $waddleRoom = null; // Not an object!
	public $room;

	public $walkingPuffle;

	public $lastPaycheck;

	public $socket;
	public $database;

	public $handshakeStep = null;
	public $ipAddress = null;

	public function __construct($socket) {
		$this->socket = $socket;
		socket_getpeername($socket, $this->ipAddress);
		// $this->database = new Kitsune\Database();
	}

	public function addCoins($coins) {
		$totalCoins = $this->coins + $coins;
		$this->setCoins($totalCoins);
		$this->send("%xt%zo%-1%$totalCoins%");
	}

	public function setCoins($coinAmount) {
		$this->coins = $coinAmount;

		$this->database->updateColumnById($this->id, "Coins", $this->coins);
	}

	public function buyIgloo($iglooId, $cost = 0) {
		array_push($this->igloos, $iglooId);

		$igloosString = implode(',', $this->igloos);

		$this->database->updateColumnById($this->id, "Igloos", $igloosString);

		if($cost !== 0) {
			$this->setCoins($this->coins - $cost);
		}

		$this->send("%xt%au%{$this->room->internalId}%$iglooId%{$this->coins}%");
	}

	public function buyFloor($floorId, $cost = 0) {
		$this->database->updateIglooColumn($this->activeIgloo, "Floor", $floorId);

		if($cost !== 0) {
			$this->setCoins($this->coins - $cost);
		}

		$this->send("%xt%ag%{$this->room->internalId}%$floorId%{$this->coins}%");
	}

	public function buyFurniture($furnitureId, $cost = 0) {
		$furnitureQuantity = 1;

		if(isset($this->furniture[$furnitureId])) {
			$furnitureQuantity = $this->furniture[$furnitureId];

			$furnitureQuantity += 1;

			if($furnitureQuantity >= 100) {
				return;
			}
		}

		$this->furniture[$furnitureId] = $furnitureQuantity;

		$furnitureString = implode(',', array_map(
			function($furnitureId, $furnitureQuantity) {
				return $furnitureId . '|' . $furnitureQuantity;
			}, array_keys($this->furniture), $this->furniture));

		$this->database->updateColumnById($this->id, "Furniture", $furnitureString);

		if($cost !== 0) {
			$this->setCoins($this->coins - $cost);
		}

		$this->send("%xt%af%{$this->room->internalId}%$furnitureId%{$this->coins}%");
	}

	public function updateColor($itemId) {
		$this->color = $itemId;
		$this->database->updateColumnById($this->id, "Color", $itemId);
		$this->room->send("%xt%upc%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateHead($itemId) {
		$this->head = $itemId;
		$this->database->updateColumnById($this->id, "Head", $itemId);
		$this->room->send("%xt%uph%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateFace($itemId) {
		$this->face = $itemId;
		$this->database->updateColumnById($this->id, "Face", $itemId);
		$this->room->send("%xt%upf%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateNeck($itemId) {
		$this->neck = $itemId;
		$this->database->updateColumnById($this->id, "Neck", $itemId);
		$this->room->send("%xt%upn%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateBody($itemId) {
		$this->body = $itemId;
		$this->database->updateColumnById($this->id, "Body", $itemId);
		$this->room->send("%xt%upb%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateHand($itemId) {
		$this->hand = $itemId;
		$this->database->updateColumnById($this->id, "Hand", $itemId);
		$this->room->send("%xt%upa%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateFeet($itemId) {
		$this->feet = $itemId;
		$this->database->updateColumnById($this->id, "Feet", $itemId);
		$this->room->send("%xt%upe%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updatePhoto($itemId) {
		$this->photo = $itemId;
		$this->database->updateColumnById($this->id, "Photo", $itemId);
		$this->room->send("%xt%upp%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function updateFlag($itemId) {
		$this->flag = $itemId;
		$this->database->updateColumnById($this->id, "Flag", $itemId);
		$this->room->send("%xt%upl%{$this->room->internalId}%{$this->id}%$itemId%");
	}

	public function addItem($itemId, $cost) {
		array_push($this->inventory, $itemId);

		$this->database->updateColumnById($this->id, "Inventory", implode('%', $this->inventory));

		if($cost !== 0) {
			$this->setCoins($this->coins - $cost);
		}

		$this->send("%xt%ai%{$this->room->internalId}%$itemId%{$this->coins}%");
	}

	public function loadPlayer() {
		$this->randomKey = null;

		$clothing = array("Color", "Head", "Face", "Neck", "Body", "Hand", "Feet", "Photo", "Flag", "Walking");
		$player = array("RegistrationDate", "Moderator", "Inventory", "Coins", "Ignores", "MinutesPlayed", "LastPaycheck");
		$igloo = array("Furniture", "Igloos");

		$columns = array_merge($clothing, $player, $igloo);
		$playerArray = $this->database->getColumnsById($this->id, $columns);

		$furnitureArray = explode(',', $playerArray["Furniture"]);

		if(!empty($furnitureArray)) {
			list($firstFurniture) = $furnitureArray;
			list($furnitureId) = explode("|", $firstFurniture);

			if($furnitureId == "") {
				array_shift($furnitureArray);

				$furniture = implode(",", $furnitureArray);

				$this->database->updateColumnById($this->id, "Furniture", $furniture);
			}

			foreach($furnitureArray as $furniture) {
				$furnitureDetails = explode('|', $furniture);
				list($furnitureId, $quantity) = $furnitureDetails;

				$this->furniture[$furnitureId] = $quantity;
			}
		}

		$ignoreArray = explode('%', $playerArray["Ignores"]);

		if(!empty($ignoreArray)) {
			list($firstIgnore) = $ignoreArray;
			list($ignoreId) = explode("|", $firstIgnore);

			if($ignoreId == "") {
				array_shift($ignoreArray);

				$ignore = implode("%", $ignoreArray);

				$this->database->updateColumnById($this->id, "Ignores", $ignore);
			}

			foreach($ignoreArray as $ignore) {
				$ignoreDetails = explode('|', $ignore);
				list($ignoreId, $ignoreName) = $ignoreDetails;

				$this->ignore[$ignoreId] = $ignoreName;
			}
		}

		$this->buddies = $this->getBuddyList();

		$igloosArray = explode(',', $playerArray["Igloos"]);
		foreach($igloosArray as $iglooType) {
			array_push($this->igloos, $iglooType);
		}

		list($this->color, $this->head, $this->face, $this->neck, $this->body, $this->hand, $this->feet, $this->photo, $this->flag) = array_values($playerArray);

		$this->age = floor((strtotime("NOW") - $playerArray["RegistrationDate"]) / 86400);
		$this->membershipDays = $this->age;

		$this->coins = $playerArray["Coins"];
		$this->moderator = (boolean)$playerArray["Moderator"];
		$this->inventory = explode('%', $playerArray["Inventory"]);

		$this->minutesPlayed = $playerArray["MinutesPlayed"];
		$this->lastPaycheck = $playerArray["LastPaycheck"];
	}

	public function getBuddyList() { // this is here because we need to use it before world login
		$buddies = $this->database->getColumnById($this->id, "Buddies");
		$buddyArray = explode('%', $buddies);

		if(!empty($buddyArray)) {
			list($firstBuddy) = $buddyArray;
			list($firstBuddyId) = explode("|", $firstBuddy);

			if($firstBuddyId == "") {
				array_shift($buddyArray);

				$buddies = implode("%", $buddyArray);

				$this->database->updateColumnById($this->id, "Buddies", $buddies);
			}

			$buddies = array();

			foreach($buddyArray as $buddy) {
				$buddyDetails = explode('|', $buddy);
				list($buddyId, $buddyName) = $buddyDetails;

				$buddies[$buddyId] = $buddyName;
			}
		}
		return $buddies;
	}

	public function getPlayerString() {
		$player = array(
			$this->id,
			$this->username,
			45,
			$this->color,
			$this->head,
			$this->face,
			$this->neck,
			$this->body,
			$this->hand,
			$this->feet,
			$this->flag,
			$this->photo,
			$this->x,
			$this->y,
			$this->frame,
			1,
			$this->membershipDays,
		);

		return implode('|', $player);
	}

	public function send($data) {
		Logger::Debug("Outgoing: $data");

		$data .= "\0";
		$bytesWritten = socket_send($this->socket, $data, strlen($data), 0);

		return $bytesWritten;
	}

}

?>
