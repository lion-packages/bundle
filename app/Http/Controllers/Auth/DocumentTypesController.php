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

	public function readDocumentTypes() {
		return (array) $this->documentTypesModel->readDocumentTypesDB();
	}

}