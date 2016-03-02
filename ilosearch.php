<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * Cpoyright (c) 2016 by Martin Bo Kristensen Grønholdt.
 * 
 */

//Root domain of galleries.
$root = "fotobiksen.dk";

//Search string from request.
$search_str = $_GET['q'];

//Array of galleries.
$galleries = array();

//Return value.
$ret = "";

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
						$doc = new DOMDocument();
						$doc->loadHTMLFile($filename);
						$title = $doc->getElementsByTagName('title');
						$title = $title->item(0)->nodeValue;
						
						$gallery = new stdClass();
						//Set title.
						$gallery->title = $title;
						//Galleries are in subdomains named the same as the directory.
						$gallery->url = $entry . "." . $root;
						
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
			//echo $i + ',';
			unset($galleries[$key]);
		}
	}
}

$ret = json_encode($galleries, 2);

echo $ret;

?>
