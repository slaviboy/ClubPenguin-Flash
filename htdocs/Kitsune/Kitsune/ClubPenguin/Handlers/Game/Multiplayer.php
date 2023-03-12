<?php

namespace Kitsune\ClubPenguin\Handlers\Game;

use Kitsune\Logging\Logger;
use Kitsune\ClubPenguin\Packets\Packet;

use Kitsune\ClubPenguin\Handlers\Game\FindFour;
use Kitsune\ClubPenguin\Handlers\Game\Mancala;
use Kitsune\ClubPenguin\Handlers\Game\TreasureHunt;

trait Multiplayer {

	// These are created in World's constructor
	public $findFourIds = array();
	public $mancalaIds = array();
	public $treasureHuntIds = array();

	public $tablePopulationById = array();
	public $playersByTableId = array();
	public $gamesByTableId = array();

	public $rinkPuck = array(0, 0, 0, 0);

	protected function handleJoinTable($socket) {
		$penguin = $this->penguins[$socket];

		$tableId = Packet::$Data[2];

		if(!isset($this->tablePopulationById[$tableId])) {
			return;
		}

		$seatId = count($this->tablePopulationById[$tableId]);

		if(in_array($tableId, $this->findFourIds)) {
			if($this->gamesByTableId[$tableId] === null) {
				$findFourGame = new FindFour();

				$this->gamesByTableId[$tableId] = $findFourGame;
			}
		}

		if(in_array($tableId, $this->mancalaIds)) {
			if($this->gamesByTableId[$tableId] === null) {
				$mancalaGame = new Mancala();

				$this->gamesByTableId[$tableId] = $mancalaGame;
			}
		}

		if(in_array($tableId, $this->treasureHuntIds)) {
			if($this->gamesByTableId[$tableId] === null) {
				$treasureHuntGame = new TreasureHunt();

				$this->gamesByTableId[$tableId] = $treasureHuntGame;
			}
		}

		$this->tablePopulationById[$tableId][$penguin->username] = $penguin;

		$seatId += 1;

		$penguin->send("%xt%jt%108%$tableId%$seatId%");

		$penguin->room->send("%xt%ut%{$penguin->room->internalId}%$tableId%$seatId%");

		$this->playersByTableId[$tableId][] = $penguin;

		$penguin->tableId = $tableId;
	}

	// TODO: Check if they're in the Ski Lodge or Attic before sending them the packet
	protected function handleGetTablePopulation($socket) {
		$penguin = $this->penguins[$socket];

		$tableIds = array_splice(Packet::$Data, 2);

		$tablePopulation = "";

		foreach($tableIds as $tableId) {
			if(isset($this->tablePopulationById[$tableId])) {
				$tablePopulation .= sprintf("%d|%d", $tableId, count($this->tablePopulationById[$tableId]));
				$tablePopulation .= "%";
			}
		}

		$penguin->send("%xt%gt%{$penguin->room->internalId}%$tablePopulation");
	}

	public function leaveTable($penguin) {
		$tableId = $penguin->tableId;

		if($tableId !== null) {
			$seatId = array_search($penguin, $this->playersByTableId[$tableId]);
			$isPlayer = array_search($penguin, $this->playersByTableId[$tableId]) < 2;

			if($isPlayer && !$this->gamesByTableId[$tableId]->gameOver) { // they're a player and the game isn't over yet
				foreach($this->playersByTableId[$tableId] as $player) {
					$player->send("%xt%cz%{$penguin->room->internalId}%{$penguin->username}%");
				}
			}

			unset($this->playersByTableId[$tableId][$seatId]);
			unset($this->tablePopulationById[$tableId][$penguin->username]);

			$penguin->room->send("%xt%ut%{$penguin->room->internalId}%$tableId%$seatId%");

			$penguin->tableId = null;

			if(count($this->playersByTableId[$tableId]) == 0) {
				$this->playersByTableId[$tableId] = array();
				$this->gamesByTableId[$tableId] = null;
			}
		}
	}

	protected function handleQuitGame($socket) {
		// Not sure if it needs implementing
	}

	protected function handleLeaveTable($socket) {
		$penguin = $this->penguins[$socket];

		$this->leaveTable($penguin);
	}

	protected function handleStartGame($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->waddleRoom !== null) {
			$waddlePlayers = array();

			if(in_array($penguin->waddleId, $this->sledRacing)) {
				foreach($penguin->room->penguins as $waddlePenguin) {
					array_push($waddlePlayers, sprintf("%s|%d|%d|%s", $waddlePenguin->username, $waddlePenguin->color, $waddlePenguin->hand, $waddlePenguin->username));
				}
				$penguin->send("%xt%uz%-1%" . sizeof($waddlePlayers) . '%' . implode('%', $waddlePlayers) . '%');
			} elseif(in_array($penguin->waddleId, $this->cardJitsu)) { // this doesn't make much sense, sorry : ~ (
				foreach($penguin->room->penguins as $seatId => $waddlePenguin) {
					if($waddlePenguin == $penguin) {
						$penguin->send("%xt%jz%{$penguin->room->internalId}%$seatId%{$penguin->username}%{$penguin->color}%0%");
					}
					array_push($waddlePlayers, sprintf("%d|%s|%d|0", $seatId, $waddlePenguin->username, $waddlePenguin->color));
				}
				$penguin->send("%xt%uz%{$penguin->room->internalId}%" . implode('%', $waddlePlayers) . "%");
				if($waddlePenguin == $penguin) {
					$penguin->room->send("%xt%sz%{$penguin->room->internalId}%");
				}
			}
		} elseif($penguin->tableId !== null) {
			$tableId = $penguin->tableId;

			$seatId = count($this->tablePopulationById[$tableId]) - 1;

			$penguin->send("%xt%jz%-1%$seatId%");

			if($seatId < 2) {
				$penguin->room->send("%xt%uz%-1%$seatId%{$penguin->username}%");

				if($seatId == 1) {
					foreach($this->playersByTableId[$tableId] as $player) {
						$player->send("%xt%sz%-1%0%");
					}
				}
			}
		}
	}

	protected function handleSendMove($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->waddleRoom !== null) {
			$waddleId = $penguin->waddleId;

			if(in_array($waddleId, $this->sledRacing)) {
				array_shift(Packet::$Data);

				$penguin->room->send("%xt%zm%" . implode('%', Packet::$Data) . '%');
			} elseif(in_array($waddleId, $this->cardJitsu)) {
				$gameInstruction = Packet::$Data[2];

				if($gameInstruction == "deal") {
					$cardCount = Packet::$Data[3];
					if(is_numeric($cardCount)) {
						$seatId = array_search($penguin, $penguin->room->penguins);
						$cardIds = array_rand($this->cards, $cardCount);

						$cards = implode('%', array_map(function($cardId) {
							$card = $this->cards[$cardId];
							array_unshift($card, rand(0, 100), $cardId);
							return implode('|', $card);
						}, $cardIds));

						$penguin->room->send("%xt%zm%{$penguin->room->internalId}%deal%$seatId%$cards%");
					}
				}
			}
		} elseif($penguin->tableId !== null) {
			$tableId = $penguin->tableId;

			$isPlayer = array_search($penguin, $this->playersByTableId[$tableId]) < 2;
			$gameReady = count($this->playersByTableId[$tableId]) >= 2;

			if($isPlayer && $gameReady) {
				if(in_array($tableId, $this->findFourIds)) {
					$chipColumn = Packet::$Data[2];
					$chipRow = Packet::$Data[3];
					$seatId = array_search($penguin, $this->playersByTableId[$tableId]);
					$libraryId = $seatId + 1;

					if($this->gamesByTableId[$tableId]->currentPlayer === $libraryId) {	// Prevents player from placing multiple chips on a single turn
							$gameStatus = $this->gamesByTableId[$tableId]->placeChip($chipColumn, $chipRow);

							foreach($this->playersByTableId[$tableId] as $recipient) {
								$recipient->send("%xt%zm%-1%$seatId%$chipColumn%$chipRow%");
							}

							$opponentSeatId = $seatId == 0 ? 1 : 0;

							if($gameStatus === FindFour::FoundFour) {
								$this->gamesByTableId[$tableId]->gameOver = true;
								$penguin->addCoins(10);

								$this->playersByTableId[$tableId][$opponentSeatId]->addCoins(5);
							} elseif($gameStatus === FindFour::Tie) {
								$this->gamesByTableId[$tableId]->gameOver = true;
								$penguin->addCoins(10);

								$this->playersByTableId[$tableId][$opponentSeatId]->addCoins(10);
							}
					} else {
						Logger::Warn("Attempted to drop multiple chips");
					}
				} elseif(in_array($tableId, $this->mancalaIds)) {
					$potIndex = Packet::$Data[2];
					$seatId = array_search($penguin, $this->playersByTableId[$tableId]);
					$libraryId = $seatId + 1;

					if($this->gamesByTableId[$tableId]->currentPlayer === $libraryId) {
						$gameStatus = $this->gamesByTableId[$tableId]->makeMove($potIndex);

						foreach($this->playersByTableId[$tableId] as $recipient) {
							if($gameStatus == Mancala::FreeTurn || $gameStatus == Mancala::Capture) {
								$recipient->send("%xt%zm%{$recipient->room->internalId}%$seatId%$potIndex%$gameStatus%");
							} else {
								$recipient->send("%xt%zm%{$recipient->room->internalId}%$seatId%$potIndex%");
							}

						}

						if($gameStatus === Mancala::Won) {
							$this->gamesByTableId[$tableId]->gameOver = true;

							$winnerSeatId = $this->gamesByTableId[$tableId]->winner - 1;
							$this->playersByTableId[$tableId][$winnerSeatId]->addCoins(10);

							$looserSeatId = $winnerSeatId == 0 ? 1 : 0;
							$this->playersByTableId[$tableId][$looserSeatId]->addCoins(5);
						} elseif($gameStatus === Mancala::Tie) {
							$this->gamesByTableId[$tableId]->gameOver = true;

							$penguin->addCoins(10);

							$opponentSeatId = $seatId == 0 ? 1 : 0;
							$this->playersByTableId[$tableId][$opponentSeatId]->addCoins(10);
						}
					} else {
						Logger::Warn("Attempted to take multiple turns");
					}
				}
			} else {
				Logger::Warn("Player {$penguin->id} is a spectator or is trying to drop a chip before connecting to a player!");
			}
		}
	}

	protected function handleGameMove($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->room->externalId == 802) {
			$this->rinkPuck = array_splice(Packet::$Data, 3);

			$puckData = implode('%', $this->rinkPuck);

			$penguin->room->send("%xt%zm%{$penguin->room->internalId}%{$penguin->id}%$puckData%");
		}
	}

	protected function handleGetGame($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->room->externalId == 802) {
			$puckData = implode('%', $this->rinkPuck);

			$penguin->send("%xt%gz%{$penguin->room->internalId}%$puckData%");
		} elseif($penguin->tableId !== null) {
			$tableId = $penguin->tableId;
			$playerUsernames = array_keys($this->tablePopulationById[$tableId]);

			@list($firstPlayer, $secondPlayer) = $playerUsernames;

			$boardString = $this->gamesByTableId[$tableId]->convertToString();

			$penguin->send("%xt%gz%-1%$firstPlayer%$secondPlayer%$boardString%");
		} elseif($penguin->waddleId !== null) {
			$waddleId = $penguin->waddleId;

			$maxUsers = sizeof($this->waddlesById[$waddleId]);
			$userCount = sizeof($this->waddleUsers[$waddleId]);

			$penguin->send("%xt%gz%-1%$maxUsers%$userCount%");
		}
	}

}

?>
