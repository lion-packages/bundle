<?php

namespace Database\Class\LionDatabase;

class Roles implements \JsonSerializable {

	private ?int $idroles = null;
	private ?string $roles_name = null;
	private ?string $roles_description = null;

	public function __construct() {

	}

	public function jsonSerialize(): mixed {
		return get_object_vars($this);
	}

	public static function capsule(): Roles {
		$roles = new Roles();

		$roles->setIdroles(
			isset(request->idroles) ? request->idroles : null
		);

		$roles->setRolesName(
			isset(request->roles_name) ? request->roles_name : null
		);

		$roles->setRolesDescription(
			isset(request->roles_description) ? request->roles_description : null
		);

		return $roles;
	}

	public function getIdroles(): ?int {
		return $this->idroles;
	}

	public function setIdroles(?int $idroles): Roles {
		$this->idroles = $idroles;
		return $this;
	}

	public function getRolesName(): ?string {
		return $this->roles_name;
	}

	public function setRolesName(?string $roles_name): Roles {
		$this->roles_name = $roles_name;
		return $this;
	}

	public function getRolesDescription(): ?string {
		return $this->roles_description;
	}

	public function setRolesDescription(?string $roles_description): Roles {
		$this->roles_description = $roles_description;
		return $this;
	}

}