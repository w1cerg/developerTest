<?php

$url = 'https://lenta.ru/rss';
$countEcho = 5;
$break = "\r\n";
$colorTitle = "\e[1;35m";
$colorDefault = "\e[0m";

if (!extension_loaded('simplexml'))
{
    die('Need simplexml extension');
}

$feeds = @simplexml_load_file($url);

if (empty($feeds) || !isset($feeds->channel) || !isset($feeds->channel->item))
{
    die('Empty feed');
}

echo $break;

foreach ($feeds->channel->item as $item)
{
    printf(
        "{$colorTitle}%s{$colorDefault} (%s){$break}%s",
        $item->title,
        $item->link,
        trim($item->description)
    );
    if (--$countEcho < 0)
    {
        break;
    }
    echo "{$break}{$break}------{$break}{$break}";
}

echo $break;