<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output encoding="utf-8" method="html"/>

<xsl:key name="tag" match="category" use="."/>

<xsl:template match="/rss">
<html>
<head>
<title><xsl:value-of select="/rss/channel/title"/></title>
</head>
<body style="padding: 20px;">
<div style="width: 20%; float: right;">
<h2>Метки</h2>
<xsl:for-each select="/rss/channel/item/category[generate-id(.)=generate-id(key('tag', .))]">
<a href="{/rss/channel/link}tag/{.}"><xsl:value-of select="."/></a>
<xsl:if test="following-sibling::category[generate-id(.)=generate-id(key('tag', .))] or parent::item/following-sibling::item/category[generate-id(.)=generate-id(key('tag', .))]">, </xsl:if>
</xsl:for-each>
</div>

<h1><xsl:value-of select="/rss/channel/title"/></h1>
<div style="margin-right: 20%; padding-right: 20px;">
<xsl:for-each select="/rss/channel/item">
    <h3><a href="{link}"><xsl:value-of select="title"/></a></h3>
    <h4><xsl:value-of select="pubDate"/></h4>
    <xsl:value-of select="description" disable-output-escaping="yes"/>
    <xsl:if test="count(child::enclosure)=1">
        <p class="mediaenclosure"><a href="{enclosure/@url}"><xsl:value-of select="child::enclosure/@url" /></a></p>
    </xsl:if>
    <hr style="margin: 20px 0;"/>
</xsl:for-each>
</div>
<p><small>RSS-лента сайта <a href="http://ibnteo.klava.org/">ibnteo.klava.org</a></small></p>
</body>
</html>
</xsl:template>

</xsl:stylesheet>
