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

$html = '<!DOCTYPE html>';
if (!empty($yaml['html-lang'])) {
    $html .= '<html lang="'.$yaml['html-lang'].'">';
} else {
    $html .= '<html lang="en">';
}
$html .= '<head>';
$html .= '<meta name="generator" content="Bitdoc">';
$html .= '<meta charset="UTF-8">';
$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
$html .= '<meta http-equiv="X-UA-Compatible" content="ie=edge">';
if (!empty($yaml['title'])) {
    $html .= '<title>'.$yaml['title'].'</title>';
}
if (!empty($yaml['description'])) {
    $html .= '<meta name="description" content="'.$yaml['description'].'">';
}

if (!empty($yaml['author'])) {
    $html .= '<meta name="author" content="'.$yaml['author'].'">';
}
$html .= '</head>';
$html .= '<body>';
$html .= '<header>';
$html .= '<h1><a href="/index.html">Bitdoc</a></h1>';
$html .= '<nav>';
$html .= '<a href="/about.html">About</a> ';
$html .= '<a href="/contact.html">Contact</a>';
$html .= '</nav>';
$html .= '</header>';
$html .= '<main">';
if (!empty($yaml['title'])) {
    $html .= '<h2>'.$yaml['title'].'</h2>';
}
if (!empty($yaml['content-comment'])) {
    $html .= '<p>'.$yaml['content-comment'].'</p>';
}
if (!empty($yaml['post-date'])) {
    $html .= '<p>';
    $html .= 'Posted on '.$yaml['post-date'];
}
if (!empty($yaml['author'])) {
    $html .= ' by '.$yaml['author'];
}
if (!empty($yaml['post-date']) or !empty($yaml['posted-by'])) {
    $html .= '. ';
}
if (!empty($yaml['modification-date'])) {
    $html .= 'Last updated on '.$yaml['modification-date'].'.';
}
if (!empty($yaml['post-date'])) {
    $html .= '</p>';
}
if (!empty($yaml['abstract'])) {
    $html .= '<p>'.$yaml['abstract'].'</p>';
}

$html .= $main_content;

$html .= '</main>';
$html .= '<footer>Footer</footer>';
$html .= '</body>';
$html .= '</html>';
