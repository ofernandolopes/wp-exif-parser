<?php
class BHExifParser {
	/**
	 * Fields to fetch from EXIF data.
	 * @var array
	 */
	public static $fieldNames = array(
		'Model' => [
			'friendly' => 'Modelo',
			'description' => 'Modelo da Câmera'
		],
		'DateTime' => [
			'friendly' => 'Data da Foto',
			'description' => 'Quando a foto foi tirada.'
		],
		'Artist' => [
			'friendly' => 'Fotógrafo',
			'description' => 'Quem tirou a foto.'
		],
		'Copyright' => [
			'friendly' => 'Direitos Autorais',
			'description' => 'Aviso de direitos autorais.'
		],
		'ExposureTime' => [
			'friendly' => 'Velocidade do Obturador',
			'description' => 'Tempo de exposição.'
		],
		'FNumber' => [
			'friendly' => 'Abertura',
			'description' => 'The aperture f-stop.'
		],
		'ISOSpeedRatings' => [
			'friendly' => 'ISO',
			'description' => 'The ISO speed.'
		],
		'FocalLength' => [
			'friendly' => 'Distância Focal',
			'description' => 'A distância focal da imagem foi tirada com (em mm).'
		]
	);

	/**
	 * EXIF values after parsing.
	 * @var Array
	 */
	private $values;

	/**
	 * File path.
	 * @var String
	 */
	private $filePath;

	/**
	 * Constructor.
	 * 
	 * @param String $filePath Path to image file.
	 */
	public function __construct($filePath) {

		foreach(self::$fieldNames as $key => $value) {
			$this->values[$key] = '';
		}

		$this->setFilePath($filePath);
	}

	/**
	 * Checks if server has support for exif parsing.
	 * @return boolean
	 */
	public static function hasSupport() {
		if(!function_exists('exif_read_data')) {	
			return false;
		}

		return true;
	}

	/**
	 * Sets the filepath to the image.
	 * @param String $filePath The filepath.
	 */
	public function setFilePath($filePath) {
		if(file_exists($filePath)) {
			$this->filePath = $filePath;
			$this->parseImageExif();
		}
	}

	/**
	 * Parses the image for EXIF data.
	 * @return void.
	 */
	private function parseImageExif() {
		$exif = exif_read_data($this->filePath);

		foreach($exif as $key => $value) {
			if(array_key_exists($key, self::$fieldNames)) {
				if($key == 'FNumber' || $key == 'FocalLength') {
					$value = $this->handleDividbleNumber($value);
				}

				if($key == 'DateTime') {
					$value = $this->handleDate($value);
				}

				$this->values[$key] = $value;
			}
		}
	}

	/**
	 * Reformats date.
	 * @param  String $date The date from the EXIF data.
	 * @return String       Reformatted date.
	 */
	private function handleDate($date) {
		$date = date_create($date);
		return date_format($date, 'Y-m-d H:i:s');
	}

	/**
	 * Handles dividble numbers (like f-stop and focal length).
	 * @param  String $number The dividble string.
	 * @return Number         The new number post division.
	 */
	private function handleDividbleNumber($number) {
		$fnumber = explode('/', $number);
		if(!isset($fnumber[0]) || !isset($fnumber[1]) || intval($fnumber[1]) == 0) {
			return $number;
		}

		return intval($fnumber[0]) / intval($fnumber[1]);
	}

	/**
	 * Returns an array with all the values.
	 * @return Array The values.
	 */
	public function getExif() {
		return $this->values;
	}

}
