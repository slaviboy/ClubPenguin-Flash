<?php

namespace Kitsune\ClubPenguin\Handlers\Play;

use Kitsune\Logging\Logger;
use Kitsune\ClubPenguin\Packets\Packet;

trait Moderation {

	public function mutePlayer($targetPlayer, $moderatorUsername) {
		if(!$targetPlayer->muted) {
			$targetPlayer->muted = true;
			Logger::Info("$moderatorUsername has muted {$targetPlayer->username}");
		} else {
			$targetPlayer->muted = false;
			Logger::Info("$moderatorUsername has unmuted {$targetPlayer->username}");
		}
	}

	public function kickPlayer($targetPlayer, $moderatorUsername) {
		foreach($this->penguins as $penguin) {
			if($penguin->moderator) {
				$penguin->send("%xt%ma%{$penguin->room->internalId}%k%{$targetPlayer->id}%{$targetPlayer->username}%");
			}
		}

		$targetPlayer->send("%xt%e%-1%5%");
		$this->removePenguin($targetPlayer);

		Logger::Info("$moderatorUsername kicked {$targetPlayer->username}");
	}

	public function banPlayer($targetPlayer, $moderatorUsername) {
		
	}

	protected function handleKickPlayerById($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->moderator) {
			$playerId = Packet::$Data[2];

			if(is_numeric($playerId)) {
				$targetPlayer = $this->getPlayerById($playerId);
				if($targetPlayer !== null) {
					$this->kickPlayer($targetPlayer, $penguin->username);
				}
			}
		}
	}

	protected function handleMutePlayerById($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->moderator) {
			$playerId = Packet::$Data[2];

			if(is_numeric($playerId)) {
				$targetPlayer = $this->getPlayerById($playerId);
				if($targetPlayer !== null) {
					$this->mutePlayer($targetPlayer, $penguin->username);
				}
			}
		}
	}

	protected function handleBanPlayerById($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->moderator) {
			$playerId = Packet::$Data[2];

			if(is_numeric($playerId)) {
				$targetPlayer = $this->getPlayerById($playerId);
				if($targetPlayer !== null) {
					$this->banPlayer($targetPlayer, $penguin->username);
				}
			}
		}
	}

	protected function handleInitBan($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->moderator) {
			$playerId = Packet::$Data[2];
			$phrase = Packet::$Data[3];

			if(is_numeric($playerId)) {
				$targetPlayer = $this->getPlayerById($playerId);

				if($targetPlayer !== null) {
					$numberOfBans = $penguin->database->getNumberOfBans($playerId);

					$penguin->send("%xt%initban%-1%{$playerId}%0%$numberOfBans%{$phrase}%{$targetPlayer->username}%");
				}
			}
		}
	}

	protected function handleModeratorBan($socket) {
		$penguin = $this->penguins[$socket];

		$player = Packet::$Data[2];
		$banType = Packet::$Data[3];
		$banReason = Packet::$Data[4];
		$banDuration = Packet::$Data[5];
		$penguinName = Packet::$Data[6];
		$banNotes = Packet::$Data[7];

		if($penguin->moderator) {
			if(is_numeric($player)) {
				$targetPlayer = $this->getPlayerById($player);
				if($targetPlayer !== null) {
					if($banDuration !== 0) {
						$targetPlayer->database->updateColumnById($targetPlayer->id, "Banned", strtotime("+".$banDuration." hours"));
					} else {
						$targetPlayer->database->updateColumnById($targetPlayer->id, "Banned", "perm");
					}

					$penguin->database->addBan($player, $penguin->username, $banNotes, $banDuration, $banType);

					$targetPlayer->send("%xt%ban%-1%$banType%$banReason%$banDuration%$banNotes%");

					$this->removePenguin($targetPlayer);

					Logger::Info("{$penguin->username} has banned {$targetPlayer->username} for $banDuration hours");
				}
			}
		}
	}

}

?>
