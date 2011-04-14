<?php
/**
 * RSS & Markdown Blog
 *
 * Blog engine on PHP, XSLT, Markdown, RSS
 *
 * @author: Vladimir Romanovich <ibnteo@gmail.com>
 * @version: 2.0 beta
 * @date: 2011/04/14
 */

define ('HPASS', '');

require ('markdown.php');

function request() {
    if (get_magic_quotes_runtime()) {
        set_magic_quotes_runtime(0);
    }
    if (get_magic_quotes_gpc()) {
        $_GET = stripslashes_array($_GET);
        $_POST = stripslashes_array($_POST);
        $_COOKIE = stripslashes_array($_COOKIE);
    }
}
function stripslashes_array($array) {
    return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}
request();


function date_diff_slash(DateTime $date1, DateTime $date2) {
    $diff = array();
    $diff[0] = ((int) $date2->format('Y')) - ((int) $date1->format('Y'));
    $diff[1] = ((int) $date2->format('n')) - ((int) $date1->format('n'));
    $diff[2] = ((int) $date2->format('j')) - ((int) $date1->format('j'));
    if($diff[2] < 0) {
        $diff[1] = $diff[1] - 1;
        $diff[2] = $diff[2] + ((int) $date1->format('t'));
    }
    if($diff[1] < 0) {
        $diff[0] = $diff[0] - 1;
        $diff[1] = $diff[1] + 12;
    }
    return join('/', $diff);
}

function site_base() {
    $script_name = split('/', $_SERVER['SCRIPT_NAME']);
    array_splice($script_name, 0, 1);
    array_splice($script_name, -1);
    $script_name[] = '';
    return 'http://'.$_SERVER['HTTP_HOST'].'/'.join('/', $script_name);
}

function site_root() {
    $script_filename = split('/', $_SERVER['SCRIPT_NAME']);
    array_splice($script_filename, 0, 1);
    array_splice($script_filename, -1);
    $script_filename[] = '';
    return '/'.join('/', $script_filename);
}

function src_href_link($matches) {
    global $blog_link;
    return $matches[1].'="'.(preg_match('/^(https?:\/\/|ftp:\/\/|mailto:)/i', $matches[2]) ? '' : $blog_link).$matches[2].'"';
}

function rss_markdown_blog() {
    global $blog_link;
    $doc = new DOMDocument();
    $doc->appendChild($doc->createElement('doc'));

    $doc->documentElement->setAttribute('base', site_base());

    $site_root = site_root();
    $doc->documentElement->setAttribute('root', $site_root);

    // auth
    session_start();
    if (!HPASS) {
        $doc->documentElement->setAttribute('hpass', '');
    }
    if (isset($_POST['password'])) {
        if (!HPASS) {
            $doc->documentElement->setAttribute('hpass', hash_hmac('sha1', $_POST['password'], 'rss-markdown-blog'));
        } else
        if (hash_hmac('sha1', $_POST['password'], 'rss-markdown-blog') == HPASS) {
            $_SESSION['auth'] = $_SERVER['REMOTE_ADDR'];
        }
    }
    if (isset($_POST['logout'])) {
        unset ($_SESSION['auth']);
    }
    $auth = false;
    if (isset($_SESSION['auth']) and $_SESSION['auth'] == $_SERVER['REMOTE_ADDR']) {
        $auth = true;
        $doc->documentElement->setAttribute('auth', 1);
    }

    // setting.xml
    $setting = new DOMDocument();
    $setting->load('setting.xml', LIBXML_DTDLOAD + LIBXML_NOENT);
    $doc->documentElement->appendChild($doc->importNode($setting->documentElement, true));

    $format_date = $setting->documentElement->getAttribute('format-date');
    $page_records = $setting->documentElement->getAttribute('page-records');
    $template = $setting->documentElement->getAttribute('template');

    $doc->documentElement->setAttribute('year', date('Y'));

    $blog_birthday = $setting->documentElement->getElementsByTagName('blog_birthday')->item(0)->nodeValue;
    $date1 = date_create($blog_birthday);
    $date2 = date_create('r');
    $doc->documentElement->setAttribute('blog-times', date_diff_slash($date1, $date2));

    // i18n_{/setting/@i18n}.xml
    $i18n = new DOMDocument();
    $i18n->load('i18n_'.$setting->documentElement->getAttribute('i18n').'.xml', LIBXML_DTDLOAD + LIBXML_NOENT);
    $doc->documentElement->appendChild($doc->importNode($i18n->documentElement, true));

    $translate_upper = $i18n->documentElement->getElementsByTagName('translate_upper')->item(0)->nodeValue;
    $translate_lower = $i18n->documentElement->getElementsByTagName('translate_lower')->item(0)->nodeValue;

    $i18n_months = array();
    $xpath = new DOMXPath($i18n);
    foreach ($xpath->query('/i18n/months/month') as $month) {
        $i18n_months[] = $month->nodeValue;
    }

    // rss.xml
    $rss = new DOMDocument();
    $rss->load('rss.xml', LIBXML_DTDLOAD + LIBXML_NOENT);
    // load xinclude
    $rss->xinclude();

    $blog_link = $rss->documentElement->getElementsByTagName('channel')->item(0)->getElementsByTagName('link')->item(0)->nodeValue;
    $doc->documentElement->setAttribute('link', $blog_link);
    if (preg_match('/[^\/]$/', $blog_link)) $blog_link .= '/';

    $blog_title = $rss->documentElement->getElementsByTagName('channel')->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
    $doc->documentElement->setAttribute('title', $blog_title);

    // save rss
    if ($auth and isset($_POST['link']) and isset($_POST['title']) and isset($_POST['description']) and isset($_POST['tags'])) {
        $xpath = new DOMXPath($rss);
        if (!$xpath->query('/rss//item[link="'.htmlspecialchars($blog_link.$_POST['link']).'"]')->item(0)) {
            $rss_save = new DOMDocument();
            $rss_save->formatOutput = true;
            $rss_save->load('rss.xml', LIBXML_DTDLOAD + LIBXML_NOENT);
            $channel = $rss_save->documentElement->getElementsByTagName('channel')->item(0);

            $item = $rss_save->createElement('item');

            $pubDate = $rss_save->createElement('pubDate', htmlspecialchars(date('r')));
            $item->appendChild($pubDate);

            $title = $rss_save->createElement('title', htmlspecialchars($_POST['title']));
            $item->appendChild($title);

            $description = $rss_save->createElement('description');
            $description_cdata = $rss_save->createCDATASection($_POST['description']);
            $description->appendChild($description_cdata);
            $item->appendChild($description);

            $link = $rss_save->createElement('link', htmlspecialchars($blog_link.$_POST['link']));
            $item->appendChild($link);

            $guid = $rss_save->createElement('guid', htmlspecialchars($blog_link.$_POST['link']));
            $guid->setAttribute('isPermaLink', 'true');
            $item->appendChild($guid);

            $categories = split(',', $_POST['tags']);
            foreach ($categories as $category) {
                $category = trim($category);
                if ($category != '') {
                    $category_node = $rss_save->createElement('category', htmlspecialchars($category));
                    $item->appendChild($category_node);
                }
            }

            $channel->insertBefore($item, $channel->getElementsByTagName('item')->item(0));
            $rss_save->save('rss.xml');
            header ('Location: '.$site_root);
            exit;
        }
    }

    {
        // tags
        $tags_max = array();
        $tag_sort = array();
        $tag_max = 0;
        $xpath = new DOMXPath($rss);
        foreach ($xpath->query('//item/category') as $item) {
            $tag = $item->nodeValue;
            if (isset($tags_max[$tag])) {
                $tags_max[$tag] ++;
            } else {
                $tags_max[$tag] = 1;
                $tag_sort[] = $tag;
            }
            if ($tag_max < $tags_max[$tag]) {
                $tag_max = $tags_max[$tag];
            }
        }
        sort($tag_sort);
        $tags = $doc->createElement('tags');
        foreach ($tag_sort as $key=>$val) {
            $category = $doc->createElement('category', $val);
            $category->setAttribute('size', ($tags_max[$val] / $tag_max * 80 + 70));
            $tags->appendChild($category);
        }
        $doc->documentElement->appendChild($tags);
    
        // format date, local link
        $xpath = new DOMXPath($rss);
        foreach ($xpath->query('//item') as $item) {
            $pubDate = $item->getElementsByTagName('pubDate')->item(0);
            $date = strtotime($pubDate->nodeValue);
            $val = str_replace('?', $i18n_months[date('m', $date) - 1], date($format_date, $date));
            $pubDate->setAttribute('val', $val);

            $link = $item->getElementsByTagName('link')->item(0);
            $link->setAttribute('loc', str_replace($blog_link, $site_root, $link->nodeValue));
        }
    }



    // content, actions
    $content = $doc->createElement('content');


    $markdown_file = '';
    if (isset($_GET['markdown'])) $markdown_file = $_GET['markdown'];
    if (isset($_POST['markdown'])) $markdown_file = $_POST['markdown'];

    // create .md
    if ($auth and isset($_POST['create_file']) and !file_exists($markdown_file.'.md')) {
        file_put_contents($_REQUEST['markdown'].'.md', '');
    }

    // ?now
    if (isset($_GET['now'])) {
        $content->appendChild($doc->createElement('html', htmlspecialchars(date('r'))));

    // ?404
    } elseif (isset($_GET['404'])) {
        header ('HTTP/1.0 404 Not Found');
        $content->appendChild($doc->createElement('ref', 'page_not_found'));

    // create
    } elseif (isset($_POST['create'])) {
        $content->appendChild($doc->createElement('create'));

    // ?markdown=?
    } elseif ($markdown_file and file_exists($markdown_file.'.md')) {
        $markdown = $doc->createElement('markdown');
        // save .md
        if ($auth and isset($_POST['text'])) {
            file_put_contents($markdown_file.'.md', $_POST['text']);
            header ('Location: '.$site_root.$markdown_file);
            exit;
        }
        $markdown->setAttribute('file', htmlspecialchars($markdown_file));
        $text = file_get_contents($markdown_file.'.md');
        $lines = split("\n", $text);
        $title = trim(preg_replace('/(^#*|#*$)/ui', '', $lines[0]));
        $cache = 'cache/'.md5($markdown_file).'.html';
        if ((!file_exists($cache)) or (filemtime($markdown_file.'.md') > filemtime($cache))) {
            $html = Markdown($text);
            @file_put_contents($cache, $html);
        } else {
            $html = file_get_contents($cache);
        }
        
        $markdown->appendChild($doc->createElement('html', htmlspecialchars($html)));
        // rss/item
        $xpath = new DOMXPath($rss);
        if ($item = $xpath->query('/rss//item[link="'.htmlspecialchars($blog_link.$markdown_file).'"]')->item(0)) {
            $markdown->appendChild($doc->importNode($item, true));
        }
        // form save
        if ($auth and isset($_POST['edit'])) {
            $markdown->setAttribute('edit', $text);
        }
        // form publish
        if ($auth and isset($_POST['publish'])) {
            $markdown->setAttribute('publish', preg_replace_callback('/(src|href)="([^"]*)"/i', 'src_href_link', $html));
        }
        $content->appendChild($markdown);
        $content->setAttribute('title', $title);

    // list
    } else {
        $xpath = new DOMXPath($rss);
        $where = '[guid]';
        // tag
        if (isset($_GET['tag'])) {
            $tag = $_GET['tag'];
            $head_title = $tag;
            $where = '[category="'.htmlspecialchars($tag).'"]';
        }
        // search
        if (isset($_GET['search'])) {
            $words = split(' ', $_GET['search']);
            $contains = '';
            foreach ($words as $word) {
                $contains .= ($contains ? ' and ' : '');
                $contains .= '(contains(translate(title,"'.$translate_upper.'","'.$translate_lower.'"), "'.htmlspecialchars(mb_strtolower($word,'utf-8')).'") or contains(translate(description,"'.$translate_upper.'","'.$translate_lower.'"), "'.htmlspecialchars(mb_strtolower($word,'utf-8')).'"))';
            }
            $where = '['.$contains.']';
        }
        // list
        $list = $doc->createElement('list');
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $i = 0;
        foreach ($xpath->query('//item'.$where) as $item) {
            if ($i >= ($page - 1) * $page_records and $i < ($page * $page_records)) {
                $list->appendChild($doc->importNode($item, true));
            }
            $i ++;
        }
        // @page
        if ($page > 1) $list->setAttribute('page-prev', $page - 1);
        if ($i > ($page * $page_records)) $list->setAttribute('page-next', $page + 1);

        $content->appendChild($list);
    }
    $doc->documentElement->appendChild($content);

    // menu
    $menu = file_get_contents('menu.md');
    $cache = 'cache/'.md5('menu').'.html';
    if ((!file_exists($cache)) or (filemtime('menu.md') > filemtime($cache))) {
        $menu = Markdown($menu);
        @file_put_contents($cache, $menu);
    } else {
        $menu = file_get_contents($cache);
    }

    $doc->documentElement->appendChild($doc->createElement('menu', htmlspecialchars($menu)));

    // xslt transform
    $xslt = new DomDocument();
    $xslt->load($template, LIBXML_DTDLOAD + LIBXML_NOENT);
    $proc = new XsltProcessor();
    $proc->importStylesheet($xslt);
    header ('Content-Type: text/html; charset=UTF-8;');
    echo ("<!DOCTYPE html>\n");
    $proc->transformToUri($doc, 'php://output');
}
// main()
rss_markdown_blog();
