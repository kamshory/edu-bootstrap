<?php

namespace Pico;

class ImportExcel
{

	/**
	 * Check if school use national ID or not
	 * @param \PHPExcel $objWorksheetSource Worksheet
	 * @param string $sheetNameSchool Sheet name for school
	 * @return bool true if school use national ID and false if school is not use national ID
	 */
	public function isUseNationalId($objWorksheetSource, $sheetNameSchool)
	{
		$useNationalId = false;
		try {
			$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetNameSchool);
			$highestRow = $objWorksheet->getHighestRow();
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

			$fieldArray = array();
			$row = 1;
			for ($col = 0; $col < $highestColumnIndex; ++$col) {
				$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
			}
			for ($row = 2; $row <= $highestRow; ++$row) {
				$data = array();
				for ($col = 0; $col < $highestColumnIndex; ++$col) {
					$data[$fieldArray[$col]] = $this->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				if (
					strtolower($data['use_national_id'])
					|| strtolower($data['use_national_id']) == 1
					|| strtolower($data['use_national_id']) == 'y'
					|| strtolower($data['use_national_id']) == 'yes'
					|| strtolower($data['use_national_id']) == 'ya'
					|| strtolower($data['use_national_id']) == 'true'
				) {
					$useNationalId = true;
					break;
				}
			}
		} catch (\Exception $e) {
			// Do nothing
		}
		return $useNationalId;
	}

	/**
	 * Validate imported data
	 * @param \PHPExcel $objWorksheetSource Worksheet
	 * @param string $sheetNameSchool Sheet name for school
	 * @param string $sheetNameStudent Sheet name for student
	 * @param string $columnNameStudent Lower case of column name name student ID
	 * @param string $sheetNameTeacher Sheet name for teacher
	 * @param string $columnNameTeacher Lower case of column name name teacher ID
	 * @return array Contain response_code and response_text
	 */
	public function validate($objWorksheetSource, $sheetNameSchool, $sheetNameStudent, $columnNameStudent, $sheetNameTeacher, $columnNameTeacher)
	{
		$useNationalId = $this->isUseNationalId($objWorksheetSource, $sheetNameSchool);
		$validData1 = true;
		$validData2 = true;
		$message = "Sukses";
		$response_code = "00";

		if ($useNationalId) {
			$validData1 = $this->validData($objWorksheetSource, $sheetNameStudent, $columnNameStudent);
			$validData2 = $this->validData($objWorksheetSource, $sheetNameTeacher, $columnNameTeacher);

			if ($useNationalId) {
				if (!$validData1 && !$validData2) {
					$message = "Data siswa dan guru tidak lengkap";
					$response_code = "05";
				} else if (!$validData1) {
					$message = "Data siswa tidak lengkap";
					$response_code = "05";
				} else if (!$validData2) {
					$message = "Data guru tidak lengkap";
					$response_code = "05";
				}
				if ($validData1 && $validData2) {
					$response_code = "00";
				}
			}
		}
		return array(
			'response_code' => $response_code,
			'response_text' => $message
		);
	}

	public function validData($objWorksheetSource, $sheetName, $columnName)
	{
		$validData = true;
		try {
			$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetName);
			$highestRow = $objWorksheet->getHighestRow();
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

			$fieldArray = array();
			$row = 1;
			for ($col = 0; $col < $highestColumnIndex; ++$col) {
				$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
			}
			for ($row = 2; $row <= $highestRow; ++$row) {
				$data = array();
				for ($col = 0; $col < $highestColumnIndex; ++$col) {
					$data[$fieldArray[$col]] = $this->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				if (empty($data[$columnName])) {
					$validData = false;
					break;
				}
			}
		} catch (\Exception $e) {
			// Do nothing
		}
		return $validData;
	}

	public function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}
}
