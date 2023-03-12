<?php

namespace Kitsune\ClubPenguin;

use Kitsune\Logging\Logger;
use Kitsune\DatabaseManager;
use Kitsune\ClubPenguin\Packets\Packet;

final class Login extends ClubPenguin {

	public $worldManager;

	public $loginAttempts;

	public function __construct() {
		parent::__construct();

		Logger::Fine("Login server is online");
	}

	protected function handleLogin($socket) {
		$penguin = $this->penguins[$socket];

		if($penguin->handshakeStep !== "randomKey") {
			return $this->removePenguin($penguin);
		}

		$this->databaseManager->add($penguin);

		$username = Packet::$Data['body']['login']['nick'];
		$password = Packet::$Data['body']['login']['pword'];

		if($penguin->database->usernameExists($username) === false) {
			$penguin->send("%xt%e%-1%100%");
			return $this->removePenguin($penguin);
		}

		$penguinData = $penguin->database->getColumnsByName($username, array("ID", "Username", "Password", "Banned"));
		$encryptedPassword = Hashing::getLoginHash($penguinData["Password"], $penguin->randomKey);

		if($encryptedPassword != $password) {

			if(!isset($this->loginAttempts[$penguin->ipAddress])) { // helps prevent the flooding of login attempts
				$this->loginAttempts[$penguin->ipAddress][$username] = array(time(), 1);
			} else {
				list($previousAttempt, $attemptCount) = $this->loginAttempts[$penguin->ipAddress][$username];
				if((time() - $previousAttempt) <= 3600) {
					$attemptCount++;
				} else {
					$attemptCount = 1;
				}

				$this->loginAttempts[$penguin->ipAddress][$username] = array(time(), $attemptCount);

				if($attemptCount > 5) {
					return $penguin->send("%xt%e%-1%150%");
				}
			}

			$penguin->send("%xt%e%-1%101%");
			return $this->removePenguin($penguin);
		} elseif($penguinData["Banned"] > strtotime("now") || $penguinData["Banned"] == "perm") {
			if(is_numeric($penguinData["Banned"])) {
				$hours = round(($penguinData["Banned"] - strtotime("now")) / ( 60 * 60 ));
				$penguin->send("%xt%e%-1%601%$hours%");
				$this->removePenguin($penguin);
			} else {
				$penguin->send("%xt%e%-1%603%");
				$this->removePenguin($penguin);
			}
		} else {

			if(isset($this->loginAttempts[$penguin->ipAddress][$username])) {
				list($previousAttempt) = $this->loginAttempts[$penguin->ipAddress][$username];
				if((time() - $previousAttempt) <= 3600) {
					return $penguin->send("%xt%e%-1%150%");
				} else {
					unset($this->loginAttempts[$penguin->ipAddress][$username]);
				}
			}

			$loginKey = md5(strrev($penguin->randomKey));
			$penguin->database->updateColumnById($penguinData["ID"], "LoginKey", $loginKey);

			$penguin->handshakeStep = "login";
			$penguin->id = $penguinData["ID"];

			$worldsString = $this->worldManager->getWorldsString();

			$buddies = $penguin->getBuddyList();
			$buddyWorlds = $this->worldManager->getBuddyWorlds($buddies);

			$penguin->send("%xt%l%-1%{$penguinData["ID"]}%$loginKey%$buddyWorlds%$worldsString%");
		}
	}

	protected function handleDisconnect($socket) {
		$penguin = $this->penguins[$socket];
		$this->removePenguin($penguin);
	}

	public function removePenguin($penguin) {
		$this->removeClient($penguin->socket);

		$this->databaseManager->remove($penguin);

		unset($this->penguins[$penguin->socket]);

		Logger::Notice("Player disconnected");
	}

}

?>
