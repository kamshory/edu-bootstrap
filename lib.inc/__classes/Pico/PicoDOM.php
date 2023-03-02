<?php

namespace Pico;

class PicoDOM //NOSONAR
{
    const BR_CLOSED = "<br />";
    const BR_CLOSE_WITH_SPACE = " <br /> ";
    const BR_OPEN = "<br>";
    const BR_OPEN_WITH_SPACE = " <br> ";
    public static function tidyHTML($buffer)
    {
        $buffer = utf8ToEntities($buffer);
        $buff = "<html><body>" . $buffer . "</body></html>";
        try {
            $domDoc = new \DOMDocument();
            $dom = $domDoc->loadHTML($buff, 0);
            if ($dom) {
                $domDoc->formatOutput = true;
                return self::getTagNode($domDoc->saveHTML(), "body");
            } else {
                return $buffer;
            }
        } catch (\Exception $e) {
            return $buffer;
        }
    }

    public static function getTagNode($data, $tag)
    {
        $result = $data;
        if (strlen($data)) {
            $closetag = "</" . $tag . ">";
            $opentag = "<" . $tag;
            $pos1 = stripos($data, $opentag, 0);
            if ($pos1 === false) {
                $result = $data;
            } else {
                $pos1 = stripos($data, ">", $pos1 + strlen($tag) - 1) + 1;
                $pos2 = stripos($data, $closetag);
                if ($pos1 === false || $pos2 === false) {
                    $result = $data;
                } else {
                    return substr($data, $pos1, ($pos2 - $pos1));
                }
            }
        }
        return $result;
    }
    public static function parseHtmlData($data, $base = "")
    {
        $ret = new \stdClass();
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);
        // get heading
        $alternateheader = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
        foreach ($alternateheader as $header) {
            $hdr = @$xpath->evaluate("/html/body//" . $header);
            for ($i = 0; $i < $hdr->length; $i++) {
                $ret->h[$header][$i] = trim($hdr->item($i)->nodeValue);
            }
        }

        // get paragraphs
        $par = @$xpath->evaluate("/html/body//p"); //NOSONAR
        $j = 0;
        for ($i = 0; $i < $par->length; $i++) {
            $p = trim(utf8ToEntities(htmlspecialchars($par->item($i)->nodeValue)), " \r\n ");
            $p = trim(preg_replace('/\s+/', ' ', $p)); //NOSONAR
            if (!empty($p)) {
                $ret->p[$j] = $p;
                $j++;
            }
        }

        // get thumbnails
        $imgs = @$xpath->evaluate("/html/body//img"); //NOSONAR
        for ($i = 0; $i < $imgs->length; $i++) {
            $img = $imgs->item($i);
            $url = $img->getAttribute('src');
            $width = $img->getAttribute('width');
            $height = $img->getAttribute('height');
            if (stripos($url, "://") === false && strlen($base)) {
                // replace base
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $ret->img[$i]['src'] = trim($newURL);
            $ret->img[$i]['width'] = trim($width);
            $ret->img[$i]['height'] = trim($height);
        }
        return $ret;
    }

    public static function filterHtmlTags($content)
    {
        global $picoEdu;
        $content = utf8ToEntities($content);
        if ($picoEdu->getProfile("filter_javascript", -1, 0)) {
            $content = strip_only_tags($content, "script", true);
        }
        if ($picoEdu->getProfile("filter_style", -1, 0)) {
            $content = strip_only_tags($content, "style", true);
        }
        if ($picoEdu->getProfile("filter_div", -1, 0)) {
            $content = strip_only_tags($content, "div", false);
        }
        if ($picoEdu->getProfile("filter_table", -1, 0)) {
            $content = strip_only_tags($content, array("table", "caption", "th", "colgroup", "thead", "tbody", "tr", "td"), false);
        }
        if ($picoEdu->getProfile("filter_frame", -1, 0)) {
            $content = strip_only_tags($content, array("frameset", "frame", "iframe"), true);
        }
        if ($picoEdu->getProfile("filter_link", -1, 0)) {
            $content = strip_only_tags($content, array("link"), true);
        }
        if ($picoEdu->getProfile("filter_event", -1, 0)) {
            $content = self::removeEvent($content, array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'));
        }
        return self::tidyHTML($content);
    }

    public static function removeEvent($sSource, $aDisabledAttributes = false)
    {
        if (!is_array($aDisabledAttributes)) {
            $aDisabledAttributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        }

        $sSource = stripcslashes($sSource);
        if (empty($aDisabledAttributes)) {
            return $sSource;
        }

        $aDisabledAttributes = @implode('|', $aDisabledAttributes);
        $sSource = preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . $aDisabledAttributes . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $sSource);
        return $sSource;
    }

    public static function stripCdata($string)
    {
        preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches);
        return str_replace($matches[0], $matches[1], $string);
    }
    public static function stripCdata2($string)
    {
        preg_match_all('/<!\-\-\[cdata\[(.*?)\]\]\-\->/is', $string, $matches);
        return str_replace($matches[0], $matches[1], $string);
    }

    public static function cerrarTag($tag, $xml)
    {
        $indice = 0;
        while ($indice < strlen($xml)) {
            $pos = stripos($xml, "<$tag", $indice);
            if ($pos !== false) {
                $posCierre = stripos($xml, ">", $pos);
                if ($xml[$posCierre - 1] == "/") {
                    $xml = substr_replace($xml, "></$tag>", $posCierre - 1, 2);
                }
                $indice = $posCierre;
            } else {
                break;
            }
        }
        if (self::brokenTags($xml) && stripos($xml, "<$tag", 0) !== false) {
            $xml .= "</$tag>";
        }
        return $xml;
    }

    public static function brokenTags($str)
    {
        preg_match_all("/(<\w+)(?:.){0,}?>/", $str, $v1); // NOSONAR
        preg_match_all("/<\/\w+>/", $str, $v2);
        $open = array_map('strtolower', $v1[1]);
        $closed = array_map('strtolower', $v2[0]);
        foreach ($open as $tag) {
            $end_tag = preg_replace("/<(.*)/", "</$1>", $tag);
            if (!in_array($end_tag, $closed)) {
                return true;
            }
            unset($closed[array_search($end_tag, $closed)]);
        }
        return false;
    }

    public static function replaceClass($data, $search, $replace) //NOSONAR
    {
        $data = self::cerrarTag("script", $data);
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);
        $blockclasses = @$xpath->evaluate("/html/body//div");
        for ($i = 0; $i < $blockclasses->length; $i++) {
            $blockclass = $blockclasses->item($i);
            $class = $blockclass->getAttribute('class');
            if (is_array($search)) {
                foreach ($search as $sr) {
                    if (strcasecmp($sr, $class) == 0) {
                        $newclass = $replace;
                        $blockclass->removeAttribute('class');
                        $blockclass->setAttribute("class", $newclass);
                    }
                }
            } else {
                $sr = $search;
                if (strcasecmp($sr, $class) == 0) {
                    $newclass = $replace;
                    $blockclass->removeAttribute('class');
                    $blockclass->setAttribute("class", $newclass);
                }
            }
        }
        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) //NOSONAR
        {
            $data = self::getTagNode($data, 'body');
        } else if (stripos($data, '<head') !== false) //NOSONAR
        {
            $data = self::getTagNode($data, 'head');
        }
        return $data;
    }

    public static function replaceUrlPrefix($data, $search, $replace, $base = "") //NOSONAR
    {
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);
        // a tag
        $hrefs = @$xpath->evaluate("/html/body//a"); //NOSONAR
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            if (stripos($url, $search, 0) === 0) {
                $newURL = $replace . substr($url, strlen($search));
            } else {
                $newURL = $url;
            }
            $href->removeAttribute('href');
            $href->setAttribute("href", $newURL);
        }
        // img tag
        $srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, $search, 0) === 0) {
                $newURL = $replace . substr($url, strlen($search));
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // audio tag
        $srcs = @$xpath->evaluate("/html/body//audio"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, $search, 0) === 0) {
                $newURL = $replace . substr($url, strlen($search));
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // video tag
        $srcs = @$xpath->evaluate("/html/body//video"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // iframe tag
        $srcs = @$xpath->evaluate("/html/body//iframe"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // frame tag
        $srcs = @$xpath->evaluate("/html/body//frame"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // param tag
        $srcs = @$xpath->evaluate("/html/body//param"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $name = $src->getAttribute('name');
            if (strtolower($name) == 'src') {
                $url = $src->getAttribute('value');
                if (stripos($url, $search, 0) === 0) {
                    $newURL = $replace . substr($url, strlen($search));
                } else {
                    $newURL = $url;
                }
                $src->removeAttribute('value');
                $src->setAttribute("value", $newURL);
            }
        }
        // object tag
        $srcs = @$xpath->evaluate("/html/body//object"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $name = $src->getAttribute('type');
            if (stripos($name, 'application') !== false) {
                $url = $src->getAttribute('data');
                if (stripos($url, $search, 0) === 0) {
                    $newURL = $replace . substr($url, strlen($search));
                } else {
                    $newURL = $url;
                }
                $src->removeAttribute('data');
                $src->setAttribute("data", $newURL);
            }
        }

        // object tag
        $srcs = @$xpath->evaluate("/html/body//embed"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, $search, 0) === 0) {
                $newURL = $replace . substr($url, strlen($search));
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) {
            $data = self::getTagNode($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = self::getTagNode($data, 'head');
        }
        return $data;
    }

    public static function replaceBase($data, $base, $target = null) //NOSONAR
    {
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);
        // a tag
        $hrefs = @$xpath->evaluate("/html/body//a");
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            $href->removeAttribute('target');
            if ($target) {
                $href->setAttribute("target", $target);
            }
            if (stripos($url, "://") === false && strlen($base)) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $href->removeAttribute('href');
            $href->setAttribute("href", $newURL);
        }
        // img tag
        $srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) //NOSONAR
            {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // audio tag
        $srcs = @$xpath->evaluate("/html/body//audio");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // video tag
        $srcs = @$xpath->evaluate("/html/body//video");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // iframe tag
        $srcs = @$xpath->evaluate("/html/body//iframe");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // frame tag
        $srcs = @$xpath->evaluate("/html/body//frame");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // param tag
        $srcs = @$xpath->evaluate("/html/body//param");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $name = $src->getAttribute('name');
            if (strtolower($name) == 'src') {
                $url = $src->getAttribute('value');
                if (stripos($url, "://") === false && strlen($base)) {
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
                $src->removeAttribute('value');
                $src->setAttribute("value", $newURL);
            }
        }
        // object tag
        $srcs = @$xpath->evaluate("/html/body//object");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $name = $src->getAttribute('type');
            if (stripos($name, 'application') !== false) {
                $url = $src->getAttribute('data');
                if (stripos($url, "://") === false && strlen($base)) {
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
                $src->removeAttribute('data');
                $src->setAttribute("data", $newURL);
            }
        }

        // object tag
        $srcs = @$xpath->evaluate("/html/body//embed");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                $newURL = $base . $url;
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) {
            $data = self::getTagNode($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = self::getTagNode($data, 'head');
        }
        return $data;
    }

    public static function replaceBaseFile($data, $base, $target = null, $onlystartedwith = null) //NOSONAR
    {
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);
        // a tag
        $hrefs = @$xpath->evaluate("/html/body//a");
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            $href->removeAttribute('target');
            if ($target) {
                $href->setAttribute("target", $target);
            }
            if (stripos($url, "://") === false && strlen($base) && (stripos($url, $onlystartedwith) === 0 || !$onlystartedwith)) {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                }
            } else {
                $newURL = $url;
            }
            $href->removeAttribute('href');
            $href->setAttribute("href", $newURL);
        }
        // img tag
        $srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base) && stripos($url, "data:") !== 0) // && (stripos($url, $onlystartedwith) === 0 || !$onlystartedwith))
            {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // audio tag
        $srcs = @$xpath->evaluate("/html/body//audio");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // video tag
        $srcs = @$xpath->evaluate("/html/body//video");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // iframe tag
        $srcs = @$xpath->evaluate("/html/body//iframe");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // frame tag
        $srcs = @$xpath->evaluate("/html/body//frame");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        // param tag
        $srcs = @$xpath->evaluate("/html/body//param");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $name = $src->getAttribute('name');
            if (strtolower($name) == 'src') {
                $url = $src->getAttribute('value');
                if (stripos($url, "://") === false && strlen($base)) {
                    if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                        $url = substr($url, strlen($onlystartedwith));
                        $newURL = $base . $url;
                    } else {
                        $newURL = $url;
                    }
                } else {
                    $newURL = $url;
                }
                $src->removeAttribute('value');
                $src->setAttribute("value", $newURL);
            }
        }
        // object tag
        $srcs = @$xpath->evaluate("/html/body//object");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $name = $src->getAttribute('type');
            if (stripos($name, 'application') !== false) {
                $url = $src->getAttribute('data');
                if (stripos($url, "://") === false && strlen($base)) {
                    if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                        $url = substr($url, strlen($onlystartedwith));
                        $newURL = $base . $url;
                    } else {
                        $newURL = $url;
                    }
                } else {
                    $newURL = $url;
                }
                $src->removeAttribute('data');
                $src->setAttribute("data", $newURL);
            }
        }

        // object tag
        $srcs = @$xpath->evaluate("/html/body//embed");
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            if (stripos($url, "://") === false && strlen($base)) {
                if ($onlystartedwith == null || ($onlystartedwith && stripos($url, $onlystartedwith) === 0)) {
                    $url = substr($url, strlen($onlystartedwith));
                    $newURL = $base . $url;
                } else {
                    $newURL = $url;
                }
            } else {
                $newURL = $url;
            }
            $src->removeAttribute('src');
            $src->setAttribute("src", $newURL);
        }
        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) {
            $data = self::getTagNode($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = self::getTagNode($data, 'head');
        }
        return $data;
    }

    public static function addFirstParagraphClass($data)
    {
        $data = self::cerrarTag("script", $data);
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);
        $blockclasses = @$xpath->evaluate("/html/body//p"); //NOSONAR
        if ($blockclasses->length > 0) {
            $blockclass = $blockclasses->item(0);
            $class = $blockclass->getAttribute('class');
            $class .= " first-paragraph";
            $class = trim($class);
            $blockclass->removeAttribute('class');
            $blockclass->setAttribute("class", $class);
        }
        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        if (stripos($data, '<body') !== false) {
            $data = self::getTagNode($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = self::getTagNode($data, 'head');
        }
        return $data;
    }


    public static function extractImageData($data, $directory, $prefix, $fileSync) //NOSONAR
    {
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);

        // img tag
        $srcs = @$xpath->evaluate("/html/body//img"); //NOSONAR
        for ($i = 0; $i < $srcs->length; $i++) {
            $src = $srcs->item($i);
            $url = $src->getAttribute('src');
            $data_latex = @$src->getAttribute('data-latex') . "";
            if ($data_latex != "") {
                $src->setAttribute("data-latex", $data_latex);
            }
            $alt = @$src->getAttribute('alt') . "";
            if ($alt != "") {
                $src->setAttribute("alt", $alt);
            }
            if (stripos($url, "data:image/") === 0) {
                $path = 0;
                $arr = explode(",", $url);
                $data = $arr[1];
                $arr1 = explode("/", $arr[0]);
                $arr2 = explode(";", $arr1[1]);
                $type = trim($arr2[0], "/;");
                unset($arr);
                $fn = md5($data);
                if (stripos($type, 'svg') !== false) {
                    $path = $directory . "/" . $fn . ".svg";
                    $filename = $prefix . "/" . $fn . ".svg";
                    $fileSync->createFileWithContent($path, base64_decode($data), true);
                    $src->removeAttribute('src');
                    $src->setAttribute("src", $filename);
                } else if (stripos($type, 'png') !== false) {
                    $path = $directory . "/" . $fn . ".png";
                    $filename = $prefix . "/" . $fn . ".png";
                    $fileSync->createFileWithContent($path, base64_decode($data), true);
                    $src->removeAttribute('src');
                    $src->setAttribute("src", $filename);
                } else {
                    $image = @imagecreatefromstring(base64_decode($data));
                    if ($image) {
                        if (!file_exists($directory)) {
                            $fileSync->createDirecory($directory, 0755, true);
                        }
                        switch ($type) {
                            case "png":
                                $path = $directory . "/" . $fn . ".png";
                                $filename = $prefix . "/" . $fn . ".png";
                                $fileSync->createFileWithContent($path, base64_decode($data), true);
                                break;
                            case "gif":
                                $path = $directory . "/" . $fn . ".gif";
                                $filename = $prefix . "/" . $fn . ".gif";
                                $fileSync->createFileWithContent($path, base64_decode($data), true);
                                break;
                            default:
                                $path = $directory . "/" . $fn . ".jpeg";
                                $filename = $prefix . "/" . $fn . ".jpeg";
                                $fileSync->createFileWithContent($path, base64_decode($data), true);
                                break;
                        }
                        $src->removeAttribute('src');
                        $src->setAttribute("src", $filename);
                    }
                }
            }
        }

        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) {
            $data = self::getTagNode($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = self::getTagNode($data, 'head');
        }
        return $data;
    }


    public static function getDataInTag($data, $tag)
    {
        $result = $data;
        if (strlen($data)) {
            $closetag = "</" . $tag . ">";
            $opentag = "<" . $tag;
            $pos1 = stripos($data, $opentag, 0);
            if ($pos1 === false) {
                $result = $data;
            } else {
                $pos1 = stripos($data, ">", $pos1 + strlen($tag) - 1) + 1;
                $pos2 = stripos($data, $closetag);
                if ($pos1 === false || $pos2 === false) {
                    $result = $data;
                } else {
                    $result = substr($data, $pos1, ($pos2 - $pos1));
                }
            }
        }
        return $result;
    }

    public static function extractParagraph($data)
    {
        $result = array();
        $dom = new \DOMDocument("1.0", "UTF-8");
        $data = utf8ToEntities($data);
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $doc = new \stdClass();
        $doc->xmlVersion = "1.0";
        $doc->xmlEncoding = "UTF-8";
        $xpath = new \DOMXPath($dom);
        // a tag
        $hrefs = @$xpath->evaluate("/html/body//p"); //NOSONAR
        for ($i = 0; $i < $hrefs->length; $i++) {
            $par = $hrefs->item($i);
            $result[$i] = $par->textContent;
        }
        return $result;
    }


    public static function filterHtml($text)
    {
        return strip_tags($text, '<iframe><img><audio><video>');
    }

    public static function firstIndex($array)
    {
        return $array[0];
    }
    public static function lastIndex($array)
    {
        return $array[count($array) - 1];
    }

    public static function addImages($text, $base_dir = '', $base_src = '', $temp_dir = "") //NOSONAR
    {
        $verticalAlign = array('baseline', 'top', 'bottom', 'middle', 'text-top', 'text-bottom');

        $base_src = ltrim(rtrim($base_src, "/") . "/", "/");
        $temp = preg_replace('/\s+/', ' ', $text);
        $temp = str_replace(self::BR_OPEN, self::BR_OPEN_WITH_SPACE, $temp);
        $temp = str_replace(self::BR_CLOSED, self::BR_CLOSE_WITH_SPACE, $temp);
        $temp = trim(preg_replace("/\s+/", " ", $temp));
        $arr = explode(" ", $temp);
        $arr_find = array();
        $arr_replace = array();
        foreach ($arr as $val) {
            if (stripos($val, "img:") === 0) {
                $val2 = trim(str_replace(self::BR_OPEN, "", $val));
                $val2 = trim(str_replace(self::BR_CLOSED, "", $val2));
                $arr2 = explode(":", $val2, 2);

                $img = trim($arr2[1]);
                $style = array();
                $style_element = '';
                if (stripos($img, "#") !== false) {
                    $arr2 = explode("#", $img);
                    $img = $arr2[0];
                    if (in_array($arr2[1], $verticalAlign)) {
                        $style[] = "vertical-align:" . $arr2[1]; //NOSONAR
                    }
                    $style_element = ' style="' . implode("; ", $style) . '"'; //NOSONAR
                    $style_element = str_replace(' style=""', '', $style_element); //NOSONAR
                    if (count($arr2) > 2) {
                        $l = explode(",", $arr2[2]);
                        $m = ((int)@$l[0]) * 1;
                        $n = ((int)@$l[1]) * 1;
                        if ($m > 0) {
                            $style_element .= ' width="' . $m . '"'; //NOSONAR
                        }
                        if ($n > 0) {
                            $style_element .= ' height="' . $n . '"'; //NOSONAR
                        }
                        if (count($arr2) > 3) {
                            $alt = rawurldecode($arr2[3]);
                            if (stripos($alt, 'latex|') === 0) {
                                $latex = substr($alt, 6);
                                $alt = $latex;
                                $style_element .= ' data-latex="' . htmlspecialchars($latex) . '"';
                                $style_element .= ' class="latex-image"';
                            }
                            $style_element .= ' alt="' . htmlspecialchars($alt) . '"';
                        }
                    }
                }

                $arr_find[] = $val2;
                $arr_replace[] = '<img src="' . $base_src . $img . '" alt="' . $img . '"' . $style_element . '>'; //NOSONAR
                if ($temp_dir != $base_dir && $temp_dir != "" && file_exists($temp_dir . "/" . $img)) {
                    @copy($temp_dir . "/" . $img, $base_dir . "/" . $img);
                }
            }
            if (stripos($val, "video:") === 0) {
                $val2 = trim(str_replace(self::BR_OPEN, "", $val));
                $val2 = trim(str_replace(self::BR_CLOSED, "", $val2));
                $arr2 = explode(":", $val2, 2);

                $img = trim($arr2[1]);
                $style = array();
                $style_element = '';
                if (stripos($img, "#") !== false) {
                    $arr2 = explode("#", $img);
                    $img = $arr2[0];
                    if (in_array($arr2[1], $verticalAlign)) {
                        $style[] = "vertical-align:" . $arr2[1];
                    }
                    $style_element = ' style="' . implode("; ", $style) . '"';
                    $style_element = str_replace(' style=""', '', $style_element);
                    if (count($arr2) > 2) {
                        $l = explode(",", $arr2[2]);
                        $m = @$l[0] * 1;
                        $n = @$l[1] * 1;
                        if ($m > 0) {
                            $style_element .= ' width="' . $m . '"';
                        } else {
                            $style_element .= ' width="500"';
                        }
                        if ($n > 0) {
                            $style_element .= ' height="' . $n . '"';
                        } else {
                            $style_element .= ' height="280"';
                        }
                    } else {
                        $style_element .= ' height="500" height="280"'; //NOSONAR
                    }
                } else {
                    $style_element .= ' height="500" height="280"';
                }

                if (stripos($img, "://") !== false) {
                    $base_src2 = "";
                } else {
                    $base_src2 = $base_src;
                }

                $arr_find[] = $val2;
                $arr_replace[] = '<video src="' . $base_src2 . $img . '" alt="' . $img . '"' . $style_element . ' controls></video>';

                if ($temp_dir != $base_dir && $temp_dir != "" && !file_exists($temp_dir . "/" . basename($img))) {
                    @copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
                }
            }
            if (stripos($val, "iframe:") === 0) {
                $val2 = trim(str_replace(self::BR_OPEN, "", $val));
                $val2 = trim(str_replace(self::BR_CLOSED, "", $val2));
                $arr2 = explode(":", $val2, 2);

                $img = trim($arr2[1]);
                $style = array();
                $style_element = '';
                if (stripos($img, "#") !== false) {
                    $arr2 = explode("#", $img);
                    $img = $arr2[0];
                    if (in_array($arr2[1], $verticalAlign)) {
                        $style[] = "vertical-align:" . $arr2[1];
                    }
                    $style_element = ' style="' . implode("; ", $style) . '"';
                    $style_element = str_replace(' style=""', '', $style_element);
                    if (count($arr2) > 2) {
                        $l = explode(",", $arr2[2]);
                        $m = @$l[0] * 1;
                        $n = @$l[1] * 1;
                        if ($m > 0) {
                            $style_element .= ' width="' . $m . '"';
                        } else {
                            $style_element .= ' width="500"';
                        }
                        if ($n > 0) {
                            $style_element .= ' height="' . $n . '"';
                        } else {
                            $style_element .= ' height="280"';
                        }
                    } else {
                        $style_element .= ' height="500" height="280"';
                    }
                } else {
                    $style_element .= ' height="500" height="280"';
                }

                if (stripos($img, "://") !== false) {
                    $base_src2 = "";
                } else {
                    $base_src2 = $base_src;
                }

                $arr_find[] = $val2;
                $arr_replace[] = '<iframe src="' . $base_src2 . $img . '" alt="' . $img . '"' . $style_element . ' allowfullscreen="allowfullscreen" frameborder="0"></iframe>';

                if ($temp_dir != $base_dir && $temp_dir != "" && !file_exists($temp_dir . "/" . basename($img))) {
                    @copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
                }
            }
            if (stripos($val, "audio:") === 0) {
                $val2 = trim(str_replace(self::BR_OPEN, "", $val));
                $val2 = trim(str_replace(self::BR_CLOSED, "", $val2));
                $arr2 = explode(":", $val2, 2);

                $img = trim($arr2[1]);
                $style = array();
                $style_element = '';
                if (stripos($img, "#") !== false) {
                    $arr2 = explode("#", $img);
                    $img = $arr2[0];
                    if (in_array($arr2[1], $verticalAlign)) {
                        $style[] = "vertical-align:" . $arr2[1];
                    }
                    $style_element = ' style="' . implode("; ", $style) . '"';
                    $style_element = str_replace(' style=""', '', $style_element);
                    if (count($arr2) > 2) {
                        $l = explode(",", $arr2[2]);
                        $m = @$l[0] * 1;
                        $n = @$l[1] * 1;
                        if ($m > 0) {
                            $style_element .= ' width="' . $m . '"';
                        } else {
                            $style_element .= ' width="300"';
                        }
                        if ($n > 0) {
                            $style_element .= ' height="' . $n . '"';
                        } else {
                            $style_element .= ' height="50"';
                        }
                    } else {
                        $style_element .= ' height="300" height="50"';
                    }
                } else {
                    $style_element .= ' height="300" height="50"';
                }

                if (stripos($img, "://") !== false) {
                    $base_src2 = "";
                } else {
                    $base_src2 = $base_src;
                }

                $arr_find[] = $val2;
                $arr_replace[] = '<audio src="' . $base_src2 . $img . '" alt="' . $img . '"' . $style_element . ' controls></audio>';

                if ($temp_dir != $base_dir && $temp_dir != "" && !file_exists($temp_dir . "/" . basename($img))) {
                    @copy($temp_dir . "/" . $img, $base_dir . "/" . basename($img));
                }
            }
            if (stripos($val, "youtube:") === 0) {
                $val2 = trim(str_replace(self::BR_OPEN, "", $val));
                $val2 = trim(str_replace(self::BR_CLOSED, "", $val2));
                $arr2 = explode(":", $val2, 2);

                $img = trim($arr2[1]);
                $style = array();
                $style[] = "border:none";
                $style_element = '';
                if (stripos($img, "#") !== false) {
                    $arr2 = explode("#", $img);
                    $img = $arr2[0];
                    if (in_array($arr2[1], $verticalAlign)) {
                        $style[] = "vertical-align:" . $arr2[1];
                    }
                    $style_element = ' style="' . implode("; ", $style) . '"';
                    $style_element = str_replace(' style=""', '', $style_element);
                }
                $arr_find[] = $val2;
                $params = self::getYoutubeParams($img);
                $video_id = $params['video_id'];
                $time = $params['time'];
                $arr_replace[] = '<iframe type="text/html" marginwidth="0" marginheight="0" scrolling="no" src="https://www.youtube.com/embed/' . $video_id . '?html5=1&playsinline=1&allowfullscreen=true&rel=0&version=3&autoplay=0&start=' . $time . '" allowfullscreen="" height="281" width="500"' . $style_element . '></iframe>';
            }
        }

        $text = str_replace(self::BR_OPEN, "\r\n", $text);
        $text = str_replace(self::BR_CLOSED, "\r\n", $text);
        $text = htmlspecialchars($text);
        if (count($arr_replace)) {
            $text = str_replace($arr_find, $arr_replace, $text);
        }

        // add question

        if (substr_count($text, "$$") > 1) {
            $arr = explode("$$", $text);
            $count = count($arr);
            for ($i = 1; $i < $count; $i += 2) {
                $txt = $arr[$i];
                $txtTrimmed = trim($txt, " \r\n\t ");
                if (strlen($txtTrimmed)) {
                    $imghtml = '<img src="../cgi-bin/equgen.cgi?' . $txt . '" class="latex-image" style="vertical-align:middle" data-latex="' . $txt . '" alt="' . $txt . '">';
                    $text = str_replace('$$' . $txt . '$$', $imghtml, $text);
                } else {
                    $text = str_replace('$$' . $txt . '$$', '', $text);
                }
            }
        }
        return $text;
    }

    public static function getYoutubeParams($url)
    {
        $s = $url;
        $params = array(
            'video_id' => '',
            'time' => '0',
            'url' => ''
        );
        if (stripos($s, "://") !== false && stripos($s, "youtube.com") !== false) {
            $s = htmlspecialchars_decode($s);
            $data = parse_url($s);
            parse_str(@$data['query'], $args);
            $vid = @$args['v'];
            $sstart = @$args['start'];

            $ff = @$data['fragment'];
            parse_str(@$ff, $fragment);
            if ($sstart == '' && @$fragment['t']) {
                $sstart = @$fragment['t'];
            }

            $sstart = str_ireplace(array("h", "m", "s"), array(" ", " ", ""), $sstart);
            $arr = explode(" ", $sstart);
            $arr2 = array_reverse($arr);
            $t = $arr2[0] + (@$arr2[1] * 60) + (@$arr2[2] * 3600);
            $time = $t;

            $yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1&amp;start=$time";

            $params['url'] = $yurl;
            $params['video_id'] = $vid;
            $params['time'] = $time;
        } else if (stripos($s, "://") !== false && stripos($s, "youtu.be") !== false) {
            $s = htmlspecialchars_decode($s);
            $data = parse_url($s);
            $vid = trim(@$data['path'], "/");
            parse_str(@$data['query'], $args);
            $sstart = @$args['t'];
            $sstart = str_ireplace(array("h", "m", "s"), array(" ", " ", ""), $sstart);
            $arr = explode(" ", $sstart);
            $arr2 = array_reverse($arr);
            $t = $arr2[0] + (@$arr2[1] * 60) + (@$arr2[2] * 3600);
            $time = $t;
            $yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1&amp;start=$time";
            $params['url'] = $yurl;
            $params['video_id'] = $vid;
            $params['time'] = $time;
        } else if (stripos($s, "://") !== false && stripos($s, "ytimg.com") !== false) {
            $s = htmlspecialchars_decode($s);
            $data = parse_url($s);
            $str = trim(@$data['path'], "/");
            $arr = explode("/", $str);
            $vid = @$arr[1];
            $yurl = "https://www.youtube.com/embed/$vid?html5=1&amp;playsinline=1&amp;allowfullscreen=true&amp;rel=0&amp;version=3&amp;autoplay=1";
            $params['url'] = $yurl;
            $params['video_id'] = $vid;
        }
        return $params;
    }

    public static function removeParagraphTag($text)
    {
        $cnt = trim($text, " \r\n\t");
        $np = substr_count(strtolower($cnt), '</p>');
        if ($np == 1 && stripos($cnt, '<p') === 0) {
            $ret = strip_only_tags($text, '<p>');
        }
        /*
        else if($np > 1)
        {
            $text = str_replace('<p', '<br />', $text);
            $text = strip_only_tags($text, '<p>');
            if(stripos($text, '<br />') === 0)
            {
                $text = substr($text, 6); 
            }
            $ret = $text;
        }
        */ else {
            $ret = $text;
        }
        return $ret;
    }
}
