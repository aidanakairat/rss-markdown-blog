<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>My Blog</title>
</head>
<body>
<?php
require('markdown.php');

$rss = new DOMDocument();//'rss.xml', LIBXML_XINCLUDE, true);
$rss->load('rss.xml');
$rss->xinclude();

$xpath = new DOMXPath($rss);
$channel_link = $xpath->evaluate('/rss/channel/link')->item(0)->nodeValue;

if (isset($_GET['now'])) {
    echo date('r');
} elseif (isset($_GET['markdown'])) {
    $markdown = file_get_contents($_GET['markdown'].'.markdown');
    echo '<p><a href="'.$channel_link.'">Main page</a></p>';
    echo Markdown($markdown);
} else {
    echo '<h1>My Blog</h1>';
    $xpath = new DOMXPath($rss);
    foreach ($xpath->query('//item') as $item) {
        $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
        $link = str_replace($channel_link, '', $item->getElementsByTagName('link')->item(0)->nodeValue);
        $date = date('m/d/Y H:i', strtotime($item->getElementsByTagName('pubDate')->item(0)->nodeValue));
        $description = $item->getElementsByTagName('description')->item(0)->nodeValue;
        echo '<p><div class="title"><a href="'.$link.'">'.$title.'</a></div><div class="date">'.$date.'</div><div class="description">'.$description.'</div></p>';
    }
}
?>
</body>
</html>
