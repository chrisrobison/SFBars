#!/usr/bin/php
<?php

$content = `egrep '1164|1180|1183|1166' bars.oem.html`;
$lines = preg_split("/\n/", $content);

$bars = [];
$types = [];

for ($i=0; $i<count($lines); $i++) {
    if (preg_match("/<span jstcache=\"1164\">(.+?)<\/span>/", $lines[$i], $matches)) {
        $barname = html_entity_decode($matches[1]);
        $next = $lines[$i + 2];
        $next2 = $lines[$i + 3];
        $stars = $lines[$i + 1];

        $bar = new stdClass();
        $bar->name = $barname;
        
        $sp = preg_split("/>\s*</", $stars);

        if (preg_match("/>(.+?)<\//", $sp[7], $m)) {
            $bar->rating = $m[1] * 10;
        }

        $parts = preg_split("/>\s</", $next);
        if (preg_match("/>(.+?)<\//", $parts[6], $matches)) {
            $bar->address = $matches[1];
        }
        if (preg_match("/>(.+?)<\//", $parts[4], $matches)) {
            $bar->type = $matches[1];
            if (!array_key_exists($matches[1], $types)) {
                $types[$matches[1]] = 1;
            } else {
                $types[$matches[1]]++;
            }
        }

        
        if (preg_match("/<span jstcache=\"1184\">(.+?)<\/span>/", $next2, $matches)) {
            $bar->notes = $matches[1];
        }

        if (preg_match("/>(.+?)<\//", $parts[2], $matches)) {
            $bar->price = $matches[1];
        }
        if (!$bar->price) {
            $bar->price = "*";
        }        
        if ($bar->address) {
            $bars[] = $bar;
        }
        $i += 2;
    }
}

$out = new stdClass();
$out->bars = $bars;
$out->types = array_keys($types);

$json = json_encode($out);

//file_put_contents("bars.json", $json);
print $json;

?>
