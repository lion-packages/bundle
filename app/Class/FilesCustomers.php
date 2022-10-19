<?php

namespace App\Class;

class FilesCustomers implements \JsonSerializable {

	public function __construct(
		private ?int $idfiles_customers = null,
		private ?string $files_customers_code = null,
		private ?string $files_customers_name = null,
		private ?string $files_customers_date = null,
		private ?string $files_customers_date_update = null,
		private ?string $files_customers_state = null,
		private ?string $files_customers_type = null,
		private ?string $files_customers_column = null
	) {}

    public function jsonSerialize(): mixed {
        return get_object_vars($this);
    }

	public function getIdfilesCustomers(): ?int {
		return $this->idfiles_customers;
	}

	public function setIdfilesCustomers(?int $idfiles_customers): FilesCustomers {
		$this->idfiles_customers = $idfiles_customers;
		return $this;
	}

	public function getFilesCustomersCode(): ?string {
		return $this->files_customers_code;
	}

	public function setFilesCustomersCode(?string $files_customers_code): FilesCustomers {
		$this->files_customers_code = $files_customers_code;
		return $this;
	}

	public function getFilesCustomersName(): ?string {
		return $this->files_customers_name;
	}

	public function setFilesCustomersName(?string $files_customers_name): FilesCustomers {
		$this->files_customers_name = $files_customers_name;
		return $this;
	}

	public function getFilesCustomersDate(): ?string {
		return $this->files_customers_date;
	}

	public function setFilesCustomersDate(?string $files_customers_date): FilesCustomers {
		$this->files_customers_date = $files_customers_date;
		return $this;
	}

	public function getFilesCustomersDateUpdate(): ?string {
		return $this->files_customers_date_update;
	}

	public function setFilesCustomersDateUpdate(?string $files_customers_date_update): FilesCustomers {
		$this->files_customers_date_update = $files_customers_date_update;
		return $this;
	}

	public function getFilesCustomersState(): ?string {
		return $this->files_customers_state;
	}

	public function setFilesCustomersState(?string $files_customers_state): FilesCustomers {
		$this->files_customers_state = $files_customers_state;
		return $this;
	}

	public function getFilesCustomersType(): ?string {
		return $this->files_customers_type;
	}

	public function setFilesCustomersType(?string $files_customers_type): FilesCustomers {
		$this->files_customers_type = $files_customers_type;
		return $this;
	}

	public function getFilesCustomersColumn(): ?string {
		return $this->files_customers_column;
	}

	public function setFilesCustomersColumn(?string $files_customers_column): FilesCustomers {
		$this->files_customers_column = $files_customers_column;
		return $this;
	}

}