<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\DocumentTypesModel;
use App\Models\Class\DocumentTypes;

class DocumentTypesController extends Controller {

	private DocumentTypesModel $documentTypesModel;

	public function __construct() {
		$this->documentTypesModel = new DocumentTypesModel();
	}

	public function readDocumentTypes(): array {
		$list = [];
		foreach ($this->documentTypesModel->readDocumentTypesDB() as $key => $documentType) {
			$list[$key] = new DocumentTypes(
				$documentType['iddocument_types'],
				$documentType['document_types_name']
			);
		}

		return $list;
	}

}