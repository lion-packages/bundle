<?php

declare(strict_types=1);

namespace Database\Class\LionDatabase;

use JsonSerializable;

class Users implements JsonSerializable
{
	private ?int $idusers = null;
	private ?int $idroles = null;
	private ?string $users_name = null;
	private ?string $users_last_name = null;
	private ?string $users_email = null;
	private ?string $users_password = null;
	private ?string $users_code = null;
	private ?string $users_create_at = null;

	public function jsonSerialize(): mixed
	{
		return get_object_vars($this);
	}

	public static function capsule(): Users
	{
		$users = new Users();

		$users->setIdusers(
			isset(request->idusers) ? request->idusers : null
		);

		$users->setIdroles(
			isset(request->idroles) ? request->idroles : null
		);

		$users->setUsersName(
			isset(request->users_name) ? request->users_name : null
		);

		$users->setUsersLastName(
			isset(request->users_last_name) ? request->users_last_name : null
		);

		$users->setUsersEmail(
			isset(request->users_email) ? request->users_email : null
		);

		$users->setUsersPassword(
			isset(request->users_password) ? request->users_password : null
		);

		$users->setUsersCode(
			isset(request->users_code) ? request->users_code : null
		);

		$users->setUsersCreateAt(
			isset(request->users_create_at) ? request->users_create_at : null
		);

		return $users;
	}

	public function getIdusers(): ?int
	{
		return $this->idusers;
	}

	public function setIdusers(?int $idusers): Users
	{
		$this->idusers = $idusers;
		return $this;
	}

	public function getIdroles(): ?int
	{
		return $this->idroles;
	}

	public function setIdroles(?int $idroles): Users
	{
		$this->idroles = $idroles;
		return $this;
	}

	public function getUsersName(): ?string
	{
		return $this->users_name;
	}

	public function setUsersName(?string $users_name): Users
	{
		$this->users_name = $users_name;
		return $this;
	}

	public function getUsersLastName(): ?string
	{
		return $this->users_last_name;
	}

	public function setUsersLastName(?string $users_last_name): Users
	{
		$this->users_last_name = $users_last_name;
		return $this;
	}

	public function getUsersEmail(): ?string
	{
		return $this->users_email;
	}

	public function setUsersEmail(?string $users_email): Users
	{
		$this->users_email = $users_email;
		return $this;
	}

	public function getUsersPassword(): ?string
	{
		return $this->users_password;
	}

	public function setUsersPassword(?string $users_password): Users
	{
		$this->users_password = $users_password;
		return $this;
	}

	public function getUsersCode(): ?string
	{
		return $this->users_code;
	}

	public function setUsersCode(?string $users_code): Users
	{
		$this->users_code = $users_code;
		return $this;
	}

	public function getUsersCreateAt(): ?string
	{
		return $this->users_create_at;
	}

	public function setUsersCreateAt(?string $users_create_at): Users
	{
		$this->users_create_at = $users_create_at;
		return $this;
	}
}
