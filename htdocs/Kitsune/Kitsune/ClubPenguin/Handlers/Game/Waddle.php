<?php

namespace Kitsune\ClubPenguin\Handlers\Game;

use Kitsune\ClubPenguin\Room;
use Kitsune\ClubPenguin\Packets\Packet;

trait Waddle {

	public $waddlesById = array(
		103 => array('', ''), 102 => array('', ''), 101 => array('', '', ''), 100 => array('', '', '', ''),
		203 => array('', ''), 202 => array('', ''), 201 => array('', ''), 200 => array('', '')
	);

	public $waddleUsers = array();

	private $sledRacing = array(100, 101, 102, 103);
	private $cardJitsu = array(200, 201, 202, 203);

	private $waddleRoomId = null;

	private $match = array();

	public $waddleRooms = array();

	protected function handleGetWaddlesPopulationById($socket) {
		$penguin = $this->penguins[$socket];

		$waddleIds = array_splice(Packet::$Data, 2);

		$waddlePopulation = implode('%', array_map(
			function($waddleId) {
				return sprintf("%d|%s", $waddleId, implode(',', $this->waddlesById[$waddleId]));
			}, $waddleIds
		));

		$penguin->send("%xt%gw%{$penguin->room->internalId}%$waddlePopulation%");
	}

	protected function handleSendJoinWaddleById($socket) {
		$penguin = $this->penguins[$socket];

		$this->leaveWaddle($penguin);

		$waddleId = Packet::$Data[2];
		$playerSeat = isset($this->waddleUsers[$waddleId]) ? sizeof($this->waddleUsers[$waddleId]) : 0;

		$this->waddleUsers[$waddleId][$playerSeat] = $penguin;
		$this->waddlesById[$waddleId][$playerSeat] = $penguin->username;

		$penguin->send("%xt%jw%{$penguin->room->internalId}%$playerSeat%");

		if($playerSeat === sizeof($this->waddlesById[$waddleId]) - 1) {
			$this->startWaddle($waddleId);
		}

		$penguin->room->send("%xt%uw%-1%$waddleId%$playerSeat%{$penguin->username}%");
	}

	private function startWaddle($waddleId) {
		foreach($this->waddlesById[$waddleId] as $seatIndex => $playerSeat) {
			$this->waddlesById[$waddleId][$seatIndex] = '';
		}

		if($this->waddleRoomId === null) {
			$this->waddleRoomId = strlen("Kitsune");
		}

		$this->waddleRoomId++;

		$roomId = $this->determineRoomId($waddleId);
		$internalId = $this->rooms[$roomId]->internalId;

		$waddleRoomId = ($this->waddleRoomId * 42) % 365;

		$this->waddleRooms[$waddleRoomId] = new Room($roomId, $internalId, false);

		$userCount = sizeof($this->waddleUsers[$waddleId]);

		foreach($this->waddleUsers[$waddleId] as $waddlePenguin) {
			$waddlePenguin->waddleRoom = $waddleRoomId;
			$waddlePenguin->waddleId = $waddleId;

			if(in_array($waddleId, $this->cardJitsu)) {
				$waddlePenguin->send("%xt%scard%0%$roomId%$waddleRoomId%$userCount%");
			} else {
				$waddlePenguin->send("%xt%sw%{$waddlePenguin->room->internalId}%$roomId%$waddleRoomId%$userCount%");
			}

		}

		$this->waddleUsers[$waddleId] = array();
	}

	private function determineRoomId($waddleId) {
		switch($waddleId) {
			case 100:
			case 101:
			case 102:
			case 103:
				return 999;
			case 200:
				return 998;
		}
	}

	protected function handleLeaveWaddle($socket) {
		$penguin = $this->penguins[$socket];

		$this->leaveWaddle($penguin);
	}

	public function leaveWaddle($penguin) {
		foreach($this->waddleUsers as $waddleId => $leWaddle) {
			foreach($leWaddle as $playerSeat => $waddlePenguin) {
				if($waddlePenguin == $penguin) {
					$penguin->room->send("%xt%uw%-1%$waddleId%$playerSeat%");

					$this->waddlesById[$waddleId][$playerSeat] = '';
					unset($this->waddleUsers[$waddleId][$playerSeat]);

					if($penguin->waddleRoom !== null) {
						$penguin->room->remove($penguin);

						if(empty($this->waddleRooms[$penguin->waddleRoom]->penguins)) {
							unset($this->waddleRooms[$penguin->waddleRoom]);
						}

						$penguin->waddleRoom = null;
						$penguin->waddleId = null;
					}

					break;
				}
			}
		}
	}

	protected function handleJoinWaddle($socket) {
		$penguin = $this->penguins[$socket];

		$penguin->room->remove($penguin);

		$roomId = Packet::$Data[2];

		if($penguin->waddleRoom !== null) {
			$this->waddleRooms[$penguin->waddleRoom]->add($penguin);
		}
	}

	protected function handleJoinMatchMaking($socket) { // this is all temporary
		$penguin = $this->penguins[$socket];

		array_push($this->match, $penguin);

		$matchReady = count($this->match) == 2;

		if($matchReady) {
			$penguin->send("%xt%jmm%0%{$penguin->username}%");
			list($firstPlayer, $secondPlayer) = $this->match;
			foreach($this->match as $player) {
				$player->send("%xt%tmm%0%-1%{$firstPlayer->username}%{$secondPlayer->username}%");
			}
			$this->waddleUsers[200] = $this->match;
			$this->startWaddle(200);
			$this->match = array();
		} else {
			$penguin->send("%xt%jmm%-1%{$penguin->username}%");
		}
	}

}

?>
