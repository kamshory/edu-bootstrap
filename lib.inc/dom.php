<?php
function extractImageData($content, $article_dir, $base_src, $fileSync)
{
	return \Pico\PicoDOM::extractImageData($content, $article_dir, $base_src, $fileSync);
}

function replaceBase($text, $base)
{
	return \Pico\PicoDOM::replaceBase($text, $base);
}

function parseHtmlData($html)
{
	return \Pico\PicoDOM::parseHtmlData($html);
}
