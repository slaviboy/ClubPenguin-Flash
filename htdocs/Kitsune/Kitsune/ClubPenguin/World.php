<?php

namespace Kitsune\ClubPenguin;

use Kitsune\Logging\Logger;
use Kitsune\ClubPenguin\Handlers;
use Kitsune\ClubPenguin\Packets\Packet;

final class World extends ClubPenguin {

	protected $worldHandlers = array(
		"s" => array(
			"j#js" => "handleJoinWorld",
			"j#jr" => "handleJoinRoom",
			"j#jp" => "handleJoinPlayerRoom",

			"i#gi" => "handleGetInventoryList",
			"i#ai" => "handleBuyInventory",
			"i#qpp" => "handleGetPlayerPins",
			"i#qpa" => "handleGetPlayerAwards",

			"b#gb" => "handleGetBuddyList",
			"b#br" => "handleBuddyRequest",
			"b#ba" => "handleBuddyAccept",
			"b#rb" => "handleRemoveBuddy",
			"b#bf" => "handleFindBuddy",

			"n#gn" => "handleGetIgnoreList",
			"n#an" => "handleAddIgnore",
			"n#rn" => "handleRemoveIgnore",

			"u#glr" => "handleGetLastRevision",
			"u#sp" => "handleSendPlayerMove",
			"u#sf" => "handleSendPlayerFrame",
			"u#h" => "handleSendHeartbeat",
			"u#sa" => "handleUpdatePlayerAction",
			"u#se" => "handleSendEmote",
			"u#sb" => "handlePlayerThrowBall",
			"u#ss" => "handleSafeMessage",
			"u#gp" => "handleLoadPlayerObject",
			"u#sj" => "handleSendJoke",
			"u#sl" => "handleSendLineMessage",

			"l#mg" => "handleGetMail",
			"l#mst" => "handleStartMailEngine",
			"l#ms" => "handleSendMailItem",
			"l#mc" => "handleMailChecked",
			"l#md" => "handleDeleteMailItem",
			"l#mdp" => "handleDeleteMailFromUser",

			"s#upc" => "handleSendUpdatePlayerClothing",
			"s#uph" => "handleSendUpdatePlayerClothing",
			"s#upf" => "handleSendUpdatePlayerClothing",
			"s#upn" => "handleSendUpdatePlayerClothing",
			"s#upb" => "handleSendUpdatePlayerClothing",
			"s#upa" => "handleSendUpdatePlayerClothing",
			"s#upe" => "handleSendUpdatePlayerClothing",
			"s#upp" => "handleSendUpdatePlayerClothing",
			"s#upl" => "handleSendUpdatePlayerClothing",

			"g#gm" => "handleGetActiveIgloo",
			"g#go" => "handleGetOwnedIgloos",
			"g#gf" => "handleGetFurnitureList",
			"g#af" => "handleBuyFurniture",
			"g#ag" => "handleSendBuyIglooFloor",
			"g#au" => "handleSendBuyIglooType",
			"g#ao" => "handleSendActivateIgloo",
			"g#ur" => "handleSaveIglooFurniture",
			"g#or" => "handleUnlockIgloo",
			"g#cr" => "handleLockIgloo",
			"g#gr" => "handleLoadPlayerIglooList",

			"m#sm" => "handleSendMessage",

			"o#k" => "handleKickPlayerById",
			"o#m" => "handleMutePlayerById",
			"o#b" => "handleBanPlayerById",

			"st#sse" => "handleStampAdd",
			"st#gps" => "handleGetStamps",
			"st#gmres" => "handleGetRecentStamps",
			"st#gsbcd" => "handleGetBookCover",
			"st#ssbcd" => "handleUpdateBookCover",

			"t#at" => "handleOpenPlayerBook",
			"t#rt" => "handleClosePlayerBook",

			"bh#lnbhg" => "handleLeaveGame",

			"p#pg" => "handleGetPufflesByPlayerId",
			"p#pgu" => "handleGetMyPlayerPuffles",
			"p#pn" => "handleSendAdoptPuffle",
			"p#pw" => "handleSendPuffleWalk",
			"p#pm" => "handleSendPuffleMove",
			"p#pf" => "handleSendPuffleFeed",
			"p#pt" => "handleSendPuffleTreat",
			"p#pr" => "handleSendPuffleRest",
			"p#pp" => "handleSendPufflePlay",
			"p#pb" => "handleSendPuffleBath",

			"f#epfga"	=>	"handleGetAgentStatus",
			"f#epfsa"	=>	"handleSetAgentStatus",
			"f#epfgr"	=>	"handleGetAgentPoints",
			"f#epfai"	=>	"handleAddAgentItem",
			"f#epfgm"	=>	"handleGetComMessages",

			"w#jx" => "handleJoinWaddle",

			"a#gt" => "handleGetTablePopulation",
			"a#jt" => "handleJoinTable",
			"a#lt" => "handleLeaveTable",
		),

		"z" => array(
			"gz" => "handleGetGame",
			"m" => "handleGameMove",
			"zo" => "handleGameOver",

			"gw" => "handleGetWaddlesPopulationById",
			"jw" => "handleSendJoinWaddleById",
			"lw" => "handleLeaveWaddle",
			"jz" => "handleStartGame",
			"lz" => "handleQuitGame",

			"zm" => "handleSendMove",

			"jmm" => "handleJoinMatchMaking" // now i'm a god
		)
	);

	use Handlers\Play\Navigation;
	use Handlers\Play\Item;
	use Handlers\Play\Player;
	use Handlers\Play\Mail;
	use Handlers\Play\Setting;
	use Handlers\Play\Igloo;
	use Handlers\Play\Message;
	use Handlers\Play\Moderation;
	use Handlers\Play\Pet;
	use Handlers\Play\Toy;
	use Handlers\Play\Stampbook;
	use Handlers\Play\Blackhole;
	use Handlers\Play\EPF;
	use Handlers\Play\Buddy;
	use Handlers\Play\Ignore;

	use Handlers\Game\General;
	use Handlers\Game\Multiplayer;
	use Handlers\Game\Waddle;

	public $name;
	public $capacity;

	public $items = array();
	public $pins = array();

	public $rooms = array();

	public $furniture = array();
	public $floors = array();
	public $igloos = array();

	public $gameStamps = array();
	public $epfItems = array();
	public $cards = array();

	public $spawnRooms = array();

	public $penguinsById = array();
	public $penguinsByName = array();

	public function __construct() {
		parent::__construct();

		if(is_dir("crumbs") === false) {
			mkdir("crumbs", 0777);
		}

		$downloadAndDecode = function($url) {
			$filename = basename($url, ".json");

			if(file_exists("crumbs/$filename.json")) {
				$jsonData = file_get_contents("crumbs/$filename.json");
			} else {
				$jsonData = file_get_contents($url);
				file_put_contents("crumbs/$filename.json", $jsonData);
			}

			$dataArray = json_decode($jsonData, true);
			return $dataArray;
		};

		$rooms = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/rooms.json");
		foreach($rooms as $room => $details) {
			$this->rooms[$room] = new Room($room, sizeof($this->rooms) + 1, ($details['path'] == '' ? true : false));
		}

		$stamps = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/stamps.json");
		foreach($stamps as $stampCat) {
			if($stampCat['parent_group_id'] == 8) {
				foreach($stampCat['stamps'] as $stamp) {
					foreach($rooms as $room){
						if(str_replace("Games : ", "", $stampCat['display']) == $room['display_name']) {
							$roomId = $room['room_id'];
						}
					}

					$this->gameStamps[$roomId][] = $stamp['stamp_id'];
				}
			}
		}

		unset($rooms);
		unset($stamps);

		$agentRooms = array(210, 212, 323, 803);
		$rockhoppersShip = array(420, 421, 422, 423);
		$ninjaRooms = array(320, 321, 324, 326);

		$noSpawn = array_merge($agentRooms, $rockhoppersShip, $ninjaRooms);
		$this->spawnRooms = array(320); /*array_keys(
			array_filter($this->rooms, function($room) use ($noSpawn) {
				if(!in_array($room->externalId, $noSpawn) && $room->externalId <= 810) {
					return true;
				}
			})
		);*/

		$items = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/paper_items.json");
		foreach($items as $itemIndex => $item) {
			$itemId = $item["paper_item_id"];

			$this->items[$itemId] = $item["cost"];

			if($item["type"] == 8) {
				array_push($this->pins, $itemId);
			}

			if(isset($item['is_epf'])) {
				$this->epfItems[$item["paper_item_id"]] = $item["cost"];
			}

			unset($items[$itemIndex]);
		}

		$furnitureList = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/furniture_items.json");
		foreach($furnitureList as $furnitureIndex => $furniture) {
			$furnitureId = $furniture["furniture_item_id"];
			$this->furniture[$furnitureId] = $furniture["cost"];

			unset($furnitureList[$furnitureIndex]);
		}

		$floors = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/igloo_floors.json");
		foreach($floors as $floorIndex => $floor) {
			$floorId = $floor["igloo_floor_id"];
			$this->floors[$floorId] = $floor["cost"];

			unset($floors[$floorIndex]);
		}

		$igloos = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/igloos.json");
		foreach($igloos as $iglooId => $igloo) {
			$this->igloos[$iglooId] = $igloo["cost"];

			unset($igloos[$iglooId]);
		}

		$cards = $downloadAndDecode("http://media1.clubpenguin.com/play/en/web_service/game_configs/cards.json");
		foreach($cards as $cardIndex => $card) {
			$cardId = $card["card_id"];
			$this->cards[$cardId] = array($card["element"], $card["value"], $card["color"], $card["power_id"]);

			unset($cards[$cardIndex]);
		}

		$this->mancalaIds = range(100, 104);
		$this->findFourIds = range(200, 207);
		$this->treasureHuntIds = range(300, 307);

		$tableIds = array_merge($this->mancalaIds, $this->findFourIds, $this->treasureHuntIds);
		$emptyTable = array();

		$this->tablePopulationById = array_fill_keys($tableIds, $emptyTable);
		$this->playersByTableId = array_fill_keys($tableIds, $emptyTable);
		$this->gamesByTableId = array_fill_keys($tableIds, null);

		Logger::Fine("World server is online");
	}

	public function getPlayerById($playerId) {
		if(isset($this->penguinsById[$playerId])) {
			return $this->penguinsById[$playerId];
		}

		return null;
	}

	public function getPlayerByName($playerName) {
		$lcPlayerName = strtolower($playerName);

		foreach($this->penguinsByName as $penguinName => $penguinObject) {
			if(strtolower($penguinName) == $lcPlayerName) {
				return $penguinObject;
			}
		}
	}

	protected function handleLogin($socket) {
		$penguin = $this->penguins[$socket];

		$this->databaseManager->add($penguin);

		$username = Packet::$Data['body']['login']['nick'];
		$loginKey = Packet::$Data['body']['login']['pword'];

		if(!$penguin->database->usernameExists($username)) {
			$penguin->send("%xt%e%-1%101%");
			return $this->removePenguin($penguin);
		}

		// Check if the player's columns match to make sure they aren't trying to spoof anything
		$penguinData = $penguin->database->getColumnsByName($username, array("ID", "Username", "LoginKey"));
		$id = $penguinData["ID"];
		$username = $penguinData["Username"];
		$dbLoginKey = $penguinData["LoginKey"];
		$loginHash = Hashing::encryptPassword($dbLoginKey . $penguin->randomKey) . $dbLoginKey;
		if($loginHash != $loginKey) {
			$penguin->send("%xt%e%-1%101%");
			return $this->removePenguin($penguin);
		} else {
			$penguin->id = $id;
			$penguin->username = $username;
			$penguin->identified = true;
			$penguin->send("%xt%l%-1%");
		}
	}

	protected function removePenguin($penguin) {
		// Remove the penguin from igloo maps if included.
		if(isset($this->openIgloos[$penguin->id])) {
			unset($this->openIgloos[$penguin->id]);
		}

		if(isset($penguin->minutesPlayed)) {
			$penguin->minutesPlayed = $penguin->minutesPlayed + round((time() - $penguin->loginTime) / 60);
			$penguin->database->updateColumnById($penguin->id, "MinutesPlayed", $penguin->minutesPlayed);
		}

		if(isset($penguin->walkingPuffle)) {
			unset($this->puffles[$penguin->walkingPuffle]);
			$penguin->updateHand(0);
		}

		// notify all online buddies that player has disconnected
		foreach($penguin->buddies as $buddyId => $buddyName) {
			$buddy = $this->getPlayerById($buddyId);
			if($buddy !== null) {
				$buddy->send("%xt%bof%{$buddy->room->internalId}%{$penguin->id}%");
			}
		}

		$this->removeClient($penguin->socket);

		if($penguin->room !== null) {
			$penguin->room->remove($penguin);
		}

		if(isset($this->penguinsById[$penguin->id])) {
			if($penguin->waddleRoom !== null) {
				$this->leaveWaddle($penguin);
			} elseif($penguin->tableId !== null) {
				$this->leaveTable($penguin);
			}

			unset($this->penguinsById[$penguin->id]);
			unset($this->penguinsByName[$penguin->username]);
		}

		$this->databaseManager->remove($penguin);

		unset($this->penguins[$penguin->socket]);
	}

	protected function handleDisconnect($socket) {
		$penguin = $this->penguins[$socket];

		$this->removePenguin($penguin);

		Logger::Info("Player disconnected");
	}

}

?>
