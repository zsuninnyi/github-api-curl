<?php

// read the files by lines
function readLines($string) {
    $lineReturns = [];
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $string) as $number => $line){
        if ($line[0] === "+" || $line[0] === "-") {
           $lineReturns[] = $number;
        }
    } 
    return $lineReturns;
}

// set the printed urls
function setURL($commits, $file, $line) {
    global $config;
    if (empty($commits) || empty($file)) {
        return false;
    }
    $main = "https://github.com";
    $url = $main . "/" . $config["username"] . "/" . $config["repo"] . "/blob";
    $urls = [];
    if (is_array($commits)) {
        foreach($commits as $commit) {
            $urls[] = $url . "/" . $commit ."/" . $file . "#L" . $line; 
        }
    }
    else {
        $urls[] = $url;
    }
    return $urls;
}

// handle the curl requests
function handleCURLProcess($url) {
    global $config;
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_USERAGENT, $config["username"]);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($process, CURLOPT_USERPWD, $config['username'].":".$config['token']);
    $respond = curl_exec($process);
    $data = json_decode($respond);
    curl_close($process);
    return $data;
}