<?php
/**
 *  This file is part of PHPLibs <http://phplibs.kmfa.net/>.
 *  
 *  Copyright (c) 2010 < squarious at gmail dot com > .
 *  
 *  PHPLibs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  PHPLibs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with PHPLibs.  If not, see <http://www.gnu.org/licenses/>.
 *  
 */


// DO NOT EDIT THIS FILE, INSTEAD DELETE IT IN PRODUCTION ENVIROMENT!
/**
 * This file is here to act as a router with extra debug information.
 */
require_once 'bootstrap.php';

$dl = Layout::create('debug')->activate();
$dl->get_document()->title = 'Error';
$dl->get_document()->add_ref_css(surl('/static/css/debug.css'));
$dl->deactivate();

function show_source_slice($file, $line)
{
    if  (! ($fh = fopen($file, "r")))
        return;
    while (!feof($fh))
        $lines[] = fgets($fh);

    fclose($fh);

    $start_line = $line - 4;
    if ($start_line < 0)
        $start_line = 0;
    $end_line = $line + 6;
    if ($end_line > count($lines))
        $end_line = count($lines);

    $code = tag('ul class="code"')->push_parent();
    for($i = $start_line; $i < $end_line; $i++)
    {   
        $fline = esc_html($lines[$i]);
        $fline = str_replace(' ', '&nbsp;', $fline);
        $fline = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $fline);
    
        $li = etag('li html_escape_off', $fline);
        if (($i + 1) == $line)
            $li->add_class('info');
    }
    Output_HTMLTag::pop_parent();
    return $code;
}

//! Show the backtrace interface
function show_backtrace_interface($exception = null)
{
    if ($exception)
    $db = $exception->getTrace();
    else
    {
        $db = debug_backtrace();
        array_shift($db);
    }

    etag('ul class="backtrace"')->push_parent();
    foreach($db as $entry)
    {   
        etag('li')->push_parent();

        etag('span class="function', $entry['function']);
        if (isset($entry['file']))
        {
            etag('span class="file"', $entry['file']);
            etag('span class="line"', (string)$entry['line']);
    
            // Code snapshot
            etag('div class="source"', show_source_slice($entry['file'], $entry['line']));
        }
        Output_HTMLTag::pop_parent();
    }
    Output_HTMLTag::pop_parent();
}

//! Manage errors
function manager_error($code, $message, $file, $line, $context)
{
    if (Layout::activated())
        Layout::activated()->deactivate();
    Layout::open('debug')->activate();
    Layout::open('debug')->get_document()->title = 'Error: ' . $message;

    etag('div class="error"', "Error",
        tag('span class="code"', (string)$code),
        tag('span class="message"', $message)
    );
    show_backtrace_interface();
    exit;
}

//! Manage exception
function manage_exception($exception)
{
    if (Layout::activated())
        Layout::activated()->deactivate();
    Layout::open('debug')->activate();
    Layout::open('debug')->get_document()->title = get_class($exception) . ': ' . $exception->getMessage();

    etag('div class="exception"', 'Exception ',
        tag('span class="type"', get_class($exception)),
        tag('span class="message"', $exception->getMessage())
    );

    show_backtrace_interface($exception);
    exit;
}

// Hook error handler
set_error_handler('manager_error');
set_exception_handler('manage_exception');

// Continue execution
require_once 'index.php';

?>
