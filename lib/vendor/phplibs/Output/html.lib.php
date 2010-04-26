<?php

//! Create a new tag
function tag()
{	$args = func_get_args();
	return call_user_func_array(
		array(new ReflectionClass('HTMLTag'), 'newInstance'),
		$args
	);
}

//! Create a tag and echo it
function etag()
{	$args = func_get_args();
	$tag = call_user_func_array('tag', $args);
	if (!$tag->append_to_default_parent())
		echo $tag;
	return $tag;
}

//! Human readable dump of a tag tree
function dump_tag($tag, $prepend = "")
{
	echo $prepend . $tag->tag . "\n";
	
	foreach ($tag->childs as $child)
	{	if (is_object($child))
			dump_tag($child, $prepend . "  ");
		else
			echo $prepend . "  " . '"' . $child . "\"\n";
	}
}

//! Human readable file size
/**
 * It is preferable to display size closer to the unit that
 * results with less digits and without using floating point. It is better
 * to use 1.2K or 1K than 1200bytes.
 * @param $size The size in bytes
 */
function html_human_fsize($size, $postfix = 'ytes')
{	if ($size < 1024)
		return $size . ' b' . $postfix;
	else if ($size < 1048576)
		return ceil($size/1024) . ' KB' . $postfix;
	else if ($size < 1073741824)
		return ceil($size/1048576) . ' MB' . $postfix;
	return ceil($size/1073741824) . ' GB' . $postfix;
}

//! Escape all html control characters from a text and return the result
function esc_html($text)
{
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

//! Escape javascript code
function esc_js($str)
{
    $str = mb_ereg_replace("\\\\", "\\\\", $str);
    $str = mb_ereg_replace("\"", "\\\"", $str);
    $str = mb_ereg_replace("'", "\\'", $str);
    $str = mb_ereg_replace("\r\n", "\\n", $str);
    $str = mb_ereg_replace("\r", "\\n", $str);
    $str = mb_ereg_replace("\n", "\\n", $str);
    $str = mb_ereg_replace("\t", "\\t", $str);
    $str = mb_ereg_replace("<", "\\x3C", $str); // for inclusion in HTML
    $str = mb_ereg_replace(">", "\\x3E", $str);
    return $str;
}

//! Add google analytics code
function html_ga_code($site_id, $return_code = false)
{
	$code = '<script type="text/javascript">';
	$code .= 'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");';
	$code .= 'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' ';
	$code .= 'type=\'text/javascript\'%3E%3C/script%3E"));';
	$code .= '</script>';
	$code .= '<script type="text/javascript">';
	$code .= 'try {';
	$code .= 'var pageTracker = _gat._getTracker("' . $site_id . '");';
	$code .= 'pageTracker._trackPageview();';
	$code .= '} catch(err) {}</script>';
	if ($return_code)
		return $code;
	
	echo $code;
	return true;
}

// Find links in html text and linkfy them
function html_linkify_urls($text, $replace_text = '<a href="${0}" target="_blank">${0}</a>')
{
	return preg_replace('/((?:http|ftp):\/\/[^\s\<\>]*)/im', $replace_text, $text);
}

?>