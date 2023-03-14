<?php

namespace Pico;

class DocxConversion
{
	private $filename = '';

	public function __construct($filePath)
	{
		$this->filename = $filePath;
	}
	private function readDoc()
	{
		$fileHandle = fopen($this->filename, "r");
		$line = @fread($fileHandle, filesize($this->filename));
		$lines = explode(chr(0x0D), $line);
		$outtext = "";
		foreach ($lines as $thisline) {
			$pos = strpos($thisline, chr(0x00));
			if (($pos !== false) || (strlen($thisline) == 0)) {
				// Do nothing
			} else {
				$outtext .= $thisline . " ";
			}
		}
		$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-@\/\_\(\)]/", "", $outtext);
		return $outtext;
	}

	private function readDocx()
	{
		$content = '';

		$zip = zip_open($this->filename);

		if (!$zip || is_numeric($zip)) {
			return false;
		}

		while ($zip_entry = zip_read($zip)) //NOSONAR
		{
			if (zip_entry_open($zip, $zip_entry) === false) //NOSONAR
			{
				continue;
			}
			if (zip_entry_name($zip_entry) != "word/document.xml") //NOSONAR
			{
				continue;
			}
			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)); //NOSONAR
			zip_entry_close($zip_entry); //NOSONAR
		} // end while

		zip_close($zip);
		$content = str_replace("</w:r></w:p></w:tc><w:tc>", "\r\n", $content);
		$content = str_replace("</w:r></w:p></w:tc><w:tc>", "\r\n", $content);
		$content = str_replace("</w:r></w:p>", "\r\n", $content);
		return strip_tags($content);
	}

	/************************excel sheet************************************/

	public function xlsxToText($input_file)
	{
		$xml_filename = "xl/sharedStrings.xml"; //content file name
		$zip_handle = new \ZipArchive;
		$output_text = "";
		if (true === $zip_handle->open($input_file)) {
			if (($xml_index = $zip_handle->locateName($xml_filename)) !== false) {
				$xml_datas = $zip_handle->getFromIndex($xml_index);
				$domDoc = new \DOMDocument();
				$xml_handle = $domDoc->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING); //NOSONAR
				$output_text = strip_tags($xml_handle->saveXML());
			} else {
				$output_text .= "";
			}
			$zip_handle->close();
		} else {
			$output_text .= "";
		}
		return $output_text;
	}

	/*************************power point files*****************************/
	public function pptxToText($input_file)
	{
		$zip_handle = new \ZipArchive;
		$output_text = "";
		if (true === $zip_handle->open($input_file)) {
			$slide_number = 1; //loop through slide files
			while (($xml_index = $zip_handle->locateName("ppt/slides/slide" . $slide_number . ".xml")) !== false) {
				$xml_datas = $zip_handle->getFromIndex($xml_index);
				$domDoc = new \DOMDocument();
				$xml_handle = $domDoc->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING); //NOSONAR
				$output_text .= strip_tags($xml_handle->saveXML());
				$slide_number++;
			}
			if ($slide_number == 1) {
				$output_text .= "";
			}
			$zip_handle->close();
		} else {
			$output_text .= "";
		}
		return $output_text;
	}

	public function convertToText() //NOSONAR
	{
		if (isset($this->filename) && !file_exists($this->filename)) {
			return "File Not exists";
		}

		$fileArray = pathinfo($this->filename);
		$file_ext  = $fileArray['extension'];
		if ($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx") {
			if ($file_ext == "doc") {
				return $this->readDoc();
			} elseif ($file_ext == "docx") {
				return $this->readDocx();
			} elseif ($file_ext == "xlsx") {
				return $this->xlsxToText($this->filename);
			} elseif ($file_ext == "pptx") {
				return $this->pptxToText($this->filename);
			}
		} else {
			return "Invalid File Type";
		}
	}
}
