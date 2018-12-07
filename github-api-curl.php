<?php

require_once "./functions.php";

$config = [
    "username" => "zsuninnyi",
    "token" => "72e086e7dc2e910d86469f2416a764a33ae8c0f4",
    "repo" => "rails/rails"
];

// get pull requests
$url = "https://api.github.com/repos/" . $config["repo"] . "/pulls";
$pullRequests = handleCURLProcess($url);
if (empty($pullRequests[0]->url)) {
    if (!empty($pullRequests->message)) {
        die($pullRequests->message);
    }
    else {
        die("Server Error");
    }
}

// get the url of each pull request
$pullRequestURLs = [];
foreach ($pullRequests as $pullRequest) {
    $pullRequestURLs[] = $pullRequest->url;
}

// iterate over the pull requests
foreach ($pullRequestURLs as $pullRequestURL) {
    echo $pullRequest->url . " branch: " . $pullRequest->title . "<br>";
    $url = $pullRequestURL . "/commits";
    $commits = handleCURLProcess($url);

    $touchedFiles = [];

    // iterate over the commits of the particular pull request
    foreach ($commits as $commit) {
        $url = "https://api.github.com/repos/" . $config["repo"] . "/commits/" . $commit->sha;
        $commit = handleCURLProcess($url);

        // figure out which files and lines changed
        foreach ($commit->files as $file) {
            if (empty($touchedFiles[$file->filename])) {
                $touchedFiles[$file->filename] = [];
                $touchedFiles[$file->filename]["count"] = 1;
                $touchedFiles[$file->filename]["lines"] = [];
            }
            else {
                $touchedFiles[$file->filename]["count"]++;
            }
            foreach (readLines($file->patch) as $line) {
                if (empty($touchedFiles[$file->filename]["lines"][$line])) {
                    $touchedFiles[$file->filename]["lines"][$line] = [];
                }
                $touchedFiles[$file->filename]["lines"][$line][] = $commit->sha;
            }
        }

    }

}

// print out the touched files and links
foreach ($touchedFiles as $filename => $file) {
    if ($file["count"] <= 1) {
        continue;
    }
    foreach ($file["lines"] as $number => $commits) {
        if (count($commits) <= 1) {
            continue;
        }
        $urls = setURL($commits, $filename, $number);
        foreach ($urls as $url) {
            echo $url . "<br/>";
        }
    }
}

