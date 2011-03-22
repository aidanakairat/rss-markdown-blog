<?php
// disqus.com
$disqus = 'example';

if (isset($_GET['404'])) {
    header ('HTTP/1.0 404 Not Found');
}

function init() {
    global $rss, $blog_link, $blog_title;
    $rss = new DOMDocument();
    $rss->load('rss.xml');
    $rss->xinclude();
    
    $xpath = new DOMXPath($rss);
    $blog_link = $xpath->evaluate('/rss/channel/link')->item(0)->nodeValue;
    $blog_title = $xpath->evaluate('/rss/channel/title')->item(0)->nodeValue;
}
init();

function tags() {
    global $rss;
    $tags = array();
    $tag_sort = array();
    $xpath = new DOMXPath($rss);
    $xpath->registerNamespace ('blog', 'rss-markdown-blog');
    $tag_max = 0;
    foreach ($xpath->query('//item/blog:tag') as $item) {
        $tag = $item->nodeValue;
        if (isset($tags[$tag])) {
            $tags[$tag] ++;
        } else {
            $tags[$tag] = 1;
            $tag_sort[] = $tag;
        }
        if ($tag_max < $tags[$tag]) {
            $tag_max = $tags[$tag];
        }
        sort($tag_sort);
    }
    $i = 0;
    foreach ($tag_sort as $key=>$val) {
        echo ($i != 0 ? ', ' : '');
        echo '<a href="/tag/'.$val.'" style="font-size: '.($tags[$val] / $tag_max * 120).'%;">'.$val.'</a>';
        $i ++;
    }
}

function content() {
    global $rss, $blog_link;
    if (isset($_GET['now'])) {
        echo date('r');
    } elseif (isset($_GET['404'])) {
        echo '<h1>Page not found</h1>';
    } elseif (isset($_GET['markdown'])) {
        require('markdown.php');
        $file = $_GET['markdown'];
        $markdown = file_get_contents($file.'.md');
        echo '<div class="content">'.Markdown($markdown).'</div>';
        $xpath = new DOMXPath($rss);
        foreach ($xpath->query('//item[link="'.htmlspecialchars($blog_link.$file).'"]') as $item) {
            $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
            $description = $item->getElementsByTagName('description')->item(0)->nodeValue;
            crosspost($blog_link.$file, $title, $description);
        }
        if ($_GET['markdown'] != 'contacts') {
            comments();
        }
    } else {
        $xpath = new DOMXPath($rss);
        $where = '';
        if (isset($_GET['tag'])) {
            $xpath->registerNamespace ('blog', 'rss-markdown-blog');
            $where = '[blog:tag="'.htmlspecialchars($_GET['tag']).'"]';
        }
        foreach ($xpath->query('//item'.$where) as $item) {
            $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
            $link = str_replace($blog_link, '/', $item->getElementsByTagName('link')->item(0)->nodeValue);
            $date = date('d.m.Y H:i', strtotime($item->getElementsByTagName('pubDate')->item(0)->nodeValue));
            $description = $item->getElementsByTagName('description')->item(0)->nodeValue;
            echo '<div class="post">';
            echo '<h2 class="title"><a href="'.$link.'" rel="bookmark">'.$title.'</a></h2>';
            echo '<div class="meta"><p>'.$date.'</p></div>';
            echo '<div class="entry">'.$description.'</div>';
            echo '<p class="comments"><a href="'.$link.'#disqus_thread"></a>&nbsp;</p>';
            echo '</div>';
        }
        comments_count();
    }
}

function crosspost($link, $title, $description) {
    echo '<p><a href="http://www.livejournal.com/update.bml?subject='.urlencode($title).'&event='.urlencode($description."\n\n".$link).'">Кросспост в ЖЖ</a></p>';
}

function comments_count() {
    global $disqus;
    echo <<<EOF
<script type="text/javascript">
    var disqus_shortname = '$disqus';
        (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
</script>
EOF;
}
function comments() {
    global $disqus;
    echo <<<EOF
<hr />
<h3>Comments</h3>
<div id="disqus_thread"></div>
<script type="text/javascript">
    var disqus_shortname = '$disqus';
    var disqus_url = window.location.href.replace('http://www.','http://');
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
EOF;
}
/*
 * Insert in Template:
 * <?php tags(); ?>
 * <?php content(); ?>
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>My Blog</title>
    <link type="text/css" rel="stylesheet" media="all" href="/style.css" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon" />
    <link rel="icon" href="/favicon.ico" type="image/gif" />
</head>
<body>
<div id="header">
    <div id="logo">
        <div id="h1"><a href="/">My Blog</a></div>
        <div id="h2" class="description">on RSS &amp; Markdown Blog</div>
    </div>
    <div id="header-icons">
        <div class="twitter"><a href="http://twitter.com/user">&nbsp;</a></div>
        <div class="rss"><a href="/rss.xml">&nbsp;</a></div>
    </div>
        
    <div id="menu">
        <div class="menu-bottom">
            <ul><li><a href="/">Home</a></li></ul>
            <div class="spacer" style="clear: both;"></div>
        </div>
    </div>
</div>

<div id="main">
    <div id="content">
    <?php content(); ?>
    </div>

<div id="sidebar1" class="sidecol">
    <ul>

<li>
    <h2>Tags</h2>
    <ul><li><?php tags(); ?></li></ul>
</li>    
<li>
    <h2>Feed on</h2>
    <ul>
      <li class="feed"><a title="RSS Feed of Posts" href="/rss.xml">Posts RSS</a></li>
    </ul>
  </li>
<li>
    <h2>Search</h2>
    <form method="get" id="searchform" action="/"> 
    <input type="text" name="search" value="Search on blog" class="with-button"
        onblur="if (this.value == '') {this.value = this.defaultValue;}"  
        onfocus="if (this.value == this.defaultValue) {this.value = '';}" /> 
        <input type="submit" value="Go" class="go" />
    </form>
  </li>
<li>
    <h2>Links</h2>
    <ul>
        <li><a href="http://code.google.com/p/rss-markdown-blog/">RSS &amp; Markdown Blog</a></li>
    </ul>
</li>
</ul>
</div>

<div style="clear:both"> </div>
</div>
<div id="footer">
    <p>
        <strong>&copy; <?php echo date('Y'); ?> <a href="<?php echo $blog_link; ?>"><?php echo $blog_title; ?></a>
    </p>
</div>
<center><!-- Counters --></center>

</body>
</html>
