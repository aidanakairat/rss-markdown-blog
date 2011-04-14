<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output encoding="utf-8" method="html"/>

<xsl:template match="/doc">
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <title><xsl:if test="content/@title!=''"><xsl:value-of select="content/@title"/> - </xsl:if><xsl:value-of select="@title"/></title>
    <link rel="alternate" type="application/rss+xml" href="{/doc/@root}rss.xml" title="{@title}" />
    <link type="text/css" rel="stylesheet" media="all" href="{/doc/@root}style.css" />
    <link rel="shortcut icon" href="{/doc/@root}favicon.ico" type="image/vnd.microsoft.icon" />
    <!--<link rel="icon" href="{/doc/@root}favicon.ico" type="image/gif" />-->
</head>
<body>
<div id="header">
    <div id="logo">
        <div id="h1"><a href="{/doc/@root}"><xsl:value-of select="/doc/@title"/></a></div>
    </div>
    <div id="header-icons">
    </div>
        
    <div id="menu">
        <div class="menu-bottom">
            <ul>
                <li><a href="{/doc/@root}">Home</a></li>
                <li><a href="{/doc/@root}contacts">Contacts</a></li>
            </ul>
            <div class="spacer" style="clear: both;"></div>
        </div>
    </div>
</div>

<div id="main">
    <div id="content">
    <xsl:apply-templates select="content/*" mode="content"/>
    </div>

<div id="sidebar1" class="sidecol">
    <ul>

<li>
    <xsl:apply-templates select="/doc" mode="admin"/>
</li>
<li>
    <h2><xsl:value-of select="/doc/i18n/tags"/></h2>
    <ul><li><xsl:apply-templates select="tags/category"/></li></ul>
</li>    
<li>
    <h2>RSS</h2>
    <ul><li><a href="{/doc/@root}rss.xml">RSS Feed</a></li></ul>
</li>
<li>
    <h2><xsl:value-of select="/doc/i18n/search_title"/></h2>
    <form method="get" id="searchform" action="{/doc/@root}"> 
        <input type="text" name="search" value="{/doc/i18n/search_default}" class="with-button"
            onblur="if (this.value == '') {{this.value = this.defaultValue;}}"  
            onfocus="if (this.value == this.defaultValue) {{this.value = '';}}" /> 
        <input type="submit" value="{/doc/i18n/search}" class="go" />
    </form>
</li>
<li>
    <h2><xsl:value-of select="/doc/i18n/menu"/></h2>
    <xsl:value-of select="menu" disable-output-escaping="yes"/>
</li>
</ul>
</div>

<div style="clear:both"> </div>
</div>
<div id="footer">
    <xsl:if test="/doc/setting/@production='true'">
        <div style="float: right; margin: 7px 320px 0 0;">
            <a href="http://fastgoogle.ru/microcounter/microcounter.php?action=results&amp;id=1305"><img border="0" src="http://fastgoogle.ru/microcounter/counter/1305.gif"/></a>
        </div>
    </xsl:if>
    <p>
        <strong><a href="{/doc/@link}" title="{/doc/@link}"><xsl:value-of select="/doc/@title"/>, 2011<xsl:if test="/doc/@year &gt; 2011"><xsl:value-of select="concat('-', /doc/@year)"/></xsl:if>
        <xsl:text> </xsl:text></a>
        <xsl:text> </xsl:text><span title="{/doc/i18n/blog_birthday}"><xsl:value-of select="@blog-times"/></span></strong>
    </p>
</div>
</body>
</html>
</xsl:template>

<!--
 | Tags
 +-->
<xsl:template match="category">
<a href="{/doc/@root}tag/{.}">
<xsl:if test="ancestor::markdown"><xsl:attribute name="rel">tag</xsl:attribute></xsl:if>
<xsl:if test="@size">
<xsl:attribute name="style">font-size: <xsl:value-of select="@size"/>%;</xsl:attribute>
</xsl:if>
<xsl:value-of select="."/></a>
<xsl:if test="following-sibling::category">, </xsl:if>
</xsl:template>

<!--
 | content/html
 +-->
<xsl:template match="html" mode="content">
<xsl:value-of select="." disable-output-escaping="yes"/>
</xsl:template>

<!--
 | Ref
 +-->
<xsl:template match="ref" mode="content">
<h1><xsl:value-of select="/doc/i18n/*[name()=current()]"/></h1>
</xsl:template>

<!--
 | Markdown
 +-->
<xsl:template match="markdown" mode="content">
<xsl:value-of select="html" disable-output-escaping="yes"/>

<xsl:if test="item">
<hr/>
<xsl:if test="/doc/setting/@production='true'">
<div class="crosspost"> 
<script type="text/javascript" src="http://yandex.st/share/share.js" charset="utf-8"></script>
<div class="yashare-auto-init" data-yashareTitle="{../@title}"  data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,friendfeed,moikrug"></div>
</div>
</xsl:if>

<xsl:for-each select="item[1]">
<p class="date"><xsl:value-of select="pubDate/@val"/></p>
<xsl:if test="category"><p class="tag"><xsl:apply-templates select="category"/></p></xsl:if>
</xsl:for-each>

<hr/>
<h3><xsl:value-of select="/doc/i18n/comments"/></h3>
<div id="disqus_thread"></div>
<xsl:if test="/doc/setting/@production='true' and /doc/setting/disqus!=''">
<script type="text/javascript">
    var disqus_shortname = '<xsl:value-of select="/doc/setting/disqus"/>';
    var disqus_url = window.location.href.replace('http://www.','http://');
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
</xsl:if>
</xsl:if>
</xsl:template>


<!--
 | List
 +-->
<xsl:template match="list" mode="content">
<xsl:for-each select="item">
<div class="post">
<h2 class="title"><a href="{link/@loc}" rel="bookmark"><xsl:value-of select="title"/></a></h2>
<div class="meta"><p><xsl:value-of select="pubDate/@val"/></p></div>
<div class="entry"><xsl:value-of select="description" disable-output-escaping="yes"/>
    <p><a href="{link/@loc}"><xsl:value-of select="/doc/i18n/read"/></a></p>
</div>
<p class="comments tags">
<a href="{link/@loc}#disqus_thread"><xsl:value-of select="/doc/i18n/comments"/></a>
<span class="tag">
<xsl:apply-templates select="category"/>
</span>
</p>
</div>
</xsl:for-each>
<!-- pages -->
<p class="newer-older">
<xsl:if test="@page-prev"><a style="float: left;" href="?page={@page-prev}">&#171; <xsl:value-of select="/doc/i18n/prev_page"/></a></xsl:if>
<xsl:if test="@page-next"><a style="float: right;" href="?page={@page-next}"><xsl:value-of select="/doc/i18n/next_page"/> &#187;</a></xsl:if>
</p>
<xsl:if test="/doc/setting/@production='true' and /doc/setting/disqus!=''">
<script type="text/javascript">
    var disqus_shortname = '<xsl:value-of select="/doc/setting/disqus"/>';
        (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
</script>
</xsl:if>
</xsl:template>

<!--
 | Include admin template
 +-->
<xsl:include href="admin.xslt"/>

</xsl:stylesheet>
