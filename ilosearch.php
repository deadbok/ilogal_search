<?php
/*
 * Backend part of ilegal_search, for searching galleries.
 * 
 * Copyright (C) 2016 by Martin Bo Kristensen Grønholdt.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

header('Content-Type: application/json; charset=UTF-8');

//Root domain of galleries.
$root = "fotobiksen.dk";

//Search string from request.
$search_str = $_GET['q'];

//Array of galleries.
$galleries = array();

//Return value.
$ret = "";

//Ilo creates an HTML file with language set to en-US and containing local characters (in this case from Denmark).
//This does not work out well for PHP, this function from "piopier" on the loadHTML PHP manual page
//sets the right encoding, so that PHP does not choke.
mb_detect_order("ASCII,UTF-8,ISO-8859-1,windows-1252,iso-8859-15");
function loadHTMLprepare($url, $encod='')
{
	$content = file_get_contents($url);
	if (!empty($content)) 
	{
		if (empty($encod))
		{
			$encod  = mb_detect_encoding($content);
		}
        $headpos = mb_strpos($content,'<head>');
        if (FALSE=== $headpos)
        {
			$headpos= mb_strpos($content,'<HEAD>');
		}
		if (FALSE!== $headpos)
		{
			$headpos+=6;
			$content = mb_substr($content,0,$headpos) . '<meta http-equiv="Content-Type" content="text/html; charset='.$encod.'">' .mb_substr($content,$headpos);
		}
		$content=mb_convert_encoding($content, 'HTML-ENTITIES', $encod);
	}
	$dom = new DomDocument;
	$res = $dom->loadHTML($content);
	if (!$res) return FALSE;
	return $dom;
}

//Open current dir on the server.
if ($handle = opendir(getcwd())) {
    while (false !== ($entry = readdir($handle)))
    {
		//Every gallery has an index.html in a subdirectory
		if (is_dir($entry))
		{
			if ( strpos($entry, '.') !== 0)
			{
				$filename = getcwd() . '/' . $entry . "/index.html";
				if (is_file($filename))
				{	
					$metas = get_meta_tags($filename);
					//Generator is always "iloapp 2.1"
					if (strcmp($metas["generator"], "iloapp 2.1") === 0)
					{
						//Get the galley title from the page title.
						$doc = loadNprepare($filename);					
						$title = $doc->getElementsByTagName('title');
						$title = $title->item(0)->nodeValue;
						
						$gallery = new stdClass();
						//Set title.
						$gallery->title = $title;
						//Galleries are in subdomains named the same as the directory.
						$gallery->url = "http://" . $entry . "." . $root;
						
						$galleries[$entry] = $gallery;
					}
				}
			}
		}
    }

    closedir($handle);
}

//Sort the galleries by their keys.
ksort($galleries);

//form input might return undefined, when empty.
if (strcmp($search_str, "undefined") === 0)
{
	$search_str = '';
}
$n_galleries = count($galleries);
if (strlen($search_str) > 0)
{
	foreach ($galleries as $key => $value)
	{
		if (stripos($galleries[$key]->title, $search_str) === false)
		{
			unset($galleries[$key]);
		}
	}
}

$ret = json_encode($galleries);

echo $ret;

?>
