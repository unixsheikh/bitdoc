<?php
// MIT License
//
// Copyright (c) 2019 Unix Sheikh
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE

$html  = '<!DOCTYPE html>'."\r";

if (!empty($yaml['html-lang'])) {
    $html .= '<html lang="'.$yaml['html-lang'].'">'."\r";
} else {
    $html .= '<html lang="en">'."\r";
}

$html .= '<head>'."\r";
$html .= '<meta name="generator" content="Bitdoc">'."\r";

if (!empty($yaml['title'])) {
    $html .= '<title>'.$yaml['title'].'</title>'."\r";
}

$html .= '<meta charset="utf-8">'."\r";

if (!empty($yaml['abstract'])) {
    $html .= '<meta name="description" content="'.$yaml['abstract'].'">'."\r";
}
if (!empty($yaml['author'])) {
    $html .= '<meta name="author" content="'.$yaml['author'].'">'."\r";
}
if (!empty($yaml['robots'])) {
    $html .= '<meta name="robots" content="'.$yaml['robots'].'">'."\r";
}

$html .= '<meta name="viewport" content="width=device-width, initial-scale=1">'."\r";
$html .= '<link rel="icon" type="image/x-icon" href="/includes/img/favicon.ico">'."\r";
$html .= '<link rel="stylesheet" href="/includes/css/bootstrap.min.css">'."\r";
$html .= '<link rel="stylesheet" href="/includes/css/custom.css">'."\r";

$html .= '</head>'."\r";
$html .= '<body>'."\r";
$html .= '<div class="container-fluid">'."\r";
$html .= '<div class="row content">'."\r";
$html .= '<div class="col-sm-3 sidenav navbar-fixed-left">'."\r";
$html .= '<div class="logo"><a href="/index.html">Bitdoc</a></div>'."\r";
$html .= '<ul class="nav nav-pills nav-stacked">';

// Menu goes here.
$html .= '<li><a href="/index.html"><span class="glyphicon glyphicon-home"></span> Home</a></li>';
$html .= '<li><a href="/about.html"><span class="glyphicon glyphicon-pencil"></span> About</a></li>';
$html .= '<li><a href="/contact.html"><span class="glyphicon glyphicon-user"></span> Contact</a></li>';

$html .= '</ul>'."\r";
$html .= '<br>'."\r";
$html .= '<p class="rights">';
$html .= '<span class="label label-primary">HTML5</span> ';
$html .= '<span class="label label-warning">CSS3</span> ';
$html .= '<span class="label label-success">RWD</span><br>';
$html .= '<div class="copyright">All rights reserved</div>';
$html .= '</div>';

$html .= '<div class="col-sm-9">';

// The title for the document.
if (!empty($yaml['title'])) {
    $html .= '<h1>'.$yaml['title'].'</h1>';
}

if (!empty($yaml['content-comment'])) {
    $html .= '<h5><span class="label label-danger content-comment"><span class="glyphicon glyphicon-warning-sign"></span> '.$yaml['content-comment'].'</span></h5>';
}

// The document posted by, date and last updated date.
$html .= '<div class="posted-by">';

if (!empty($yaml['post-date'])) {
    $html .= 'Posted on '.$yaml['post-date'];
}

if (!empty($yaml['author'])) {
    $html .= ' by '.$yaml['author'];
}

// Lets get complicated. We need a DOT if "posted by" or "date" apears.
if (!empty($yaml['date']) or !empty($yaml['posted-by'])) {
    $html .= '. ';
}

if (!empty($yaml['modification-date'])) {
    $html .= '<br>Last updated on '.$yaml['modification-date'];
}

$html .= '</div>'."\r";

// The document abstract.
if (!empty($yaml['abstract'])) {
    $html .= '<div class="abstract">'.$yaml['abstract'].'</div>'."\r";
}

// This is where we place the converted Markdown content.
$html .= $main_content;

$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</body>';
$html .= '</html>';
