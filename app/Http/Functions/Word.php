<?php

namespace App\Http\Functions;

use PhpOffice\PhpWord\{ TemplateProcessor, PhpWord, IOFactory };
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Element\Section;
use App\Http\Functions\Files;

class Word {

	private static ?TemplateProcessor $templateProcessor = null;
	private static ?PhpWord $phpWord = null;
	private static ?Section $section = null;
	private static ?Font $font = null;
	
	public function __construct() {
		
	}

	public static function load(): void {
		self::$phpWord = new PhpWord();
		self::$font = new Font();
	}

	public static function save(string $path, string $file_name, array $type = [], bool $rename = false): array {
		Files::folder($path);
		$list_files = [];
		$count = 0;

		foreach ($type as $key => $ext) {
			if ($count === 0) {
				$file_name = !$rename ? "{$file_name}.{$ext}" : Files::rename("{$file_name}.{$ext}");
			} else {
				$file_name = Files::getName($file_name) . ".{$ext}";
			}

			$list_files[$ext] = "{$path}{$file_name}";
			IOFactory::createWriter(self::$phpWord, $key)->save("{$path}{$file_name}");

			$count++;
		}
		
		return $list_files;
	}

	public static function loadTemplate(string $path): void {
		self::$font = new Font();
		self::$templateProcessor = new TemplateProcessor($path);
	}

	public static function saveTemplate(string $path, string $file_name, bool $option = false): string {
		$file_name = !$option ? "{$file_name}.docx" : Files::rename("{$file_name}.docx");
		self::$templateProcessor->saveAs("{$path}{$file_name}");
		self::$templateProcessor = null;
		return "{$path}{$file_name}";
	}

	public static function convertToHtml(string $path, string $url, bool $option = false): string {
		Files::folder($url);
		$file_name = !$option ? Files::getName($path) : Files::getName($path);
		$file_name = "{$url}{$file_name}.html";

		IOFactory::createWriter(IOFactory::load($path), 'HTML')->save($file_name);
		return $file_name;
	}

	public static function add(array $elements): void {
		foreach ($elements as $key => $element) {
			self::$templateProcessor->setValue($key, $element);	
		}
	}

	public static function section(array $options = []): void {
		self::$section = self::$phpWord->addSection($options);
	}

	public static function text(string $text): void {
		self::$section->addText($text)->setFontStyle(self::$font);
		self::$font = new Font();
	}

	public static function bold(): void {
		self::$font->setBold(true);
	}

	public static function name(string $name): void {
		self::$font->setName($name);
	}

	public static function size(int $size): void {
		self::$font->setSize($size);	
	}

}