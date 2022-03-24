<?php

namespace App\Models\Class;

class DocumentTypes implements \JsonSerializable {

	public function __construct(
		private ?int $iddocument_types = null,
		private ?string $document_types_name = null
	) {}

	public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function getIddocumentTypes() {
        return $this->iddocument_types;
    }

    public function setIddocumentTypes($iddocument_types) {
        $this->iddocument_types = $iddocument_types;
        return $this;
    }

    public function getDocumentTypesName() {
        return $this->document_types_name;
    }

    public function setDocumentTypesName($document_types_name) {
        $this->document_types_name = $document_types_name;
        return $this;
    }

}