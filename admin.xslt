<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output encoding="utf-8" method="html"/>

<xsl:template match="/doc" mode="admin">
<h2><xsl:value-of select="@title"/></h2>
<xsl:if test="@hpass=''">
<p><xsl:value-of select="/doc/i18n/admin/help"/></p>
</xsl:if>
<xsl:if test="@hpass!=''">
<p><xsl:value-of select="/doc/i18n/admin/insert"/>:<br/><textarea readonly="readonly" style="height: 4em;">define ('HPASS', '<xsl:value-of select="@hpass"/>');</textarea></p>
</xsl:if>
<form action="" method="post">
<p><label><xsl:value-of select="/doc/i18n/admin/password"/>:</label>
<input class="textbox with-button" type="password" name="password"/>
<input type="submit" value="{/doc/i18n/admin/login}" class="go"/></p>
</form>
</xsl:template>

<xsl:template match="/doc[@auth]" mode="admin">
<h2><xsl:value-of select="/doc/i18n/admin/title"/></h2>
<xsl:if test="not(content/markdown[@edit or @publish]) and not(content/markdown/@file='menu') and not(content/create)">
<form action="{/doc/@root}menu" method="post">
<xsl:if test="content/markdown/@file!=''"><input type="hidden" name="markdown" value="{content/markdown/@file}"/></xsl:if>
<p><input type="submit" value="{/doc/i18n/admin/edit_menu}" style="width: 100%;" name="edit"/></p>
</form>
</xsl:if>
<form action="" method="post">
<xsl:if test="content/markdown/@file!=''"><input type="hidden" name="markdown" value="{content/markdown/@file}"/></xsl:if>
<xsl:if test="not(content/markdown[@edit or @publish]) and not(content/create) and not(content/ref)">
<p><input type="submit" value="{/doc/i18n/admin/create}" style="width: 100%;" name="create"/></p>
</xsl:if>
<xsl:if test="content/markdown[@edit or @publish] or content/create">
<p><input type="submit" value="{/doc/i18n/admin/no_save}" style="width: 100%;"/></p>
</xsl:if>
<xsl:if test="content/markdown[not(@edit or @publish)]">
<p><input type="submit" value="{/doc/i18n/admin/edit}" style="width: 100%;" name="edit"/></p>
<xsl:if test="not(content/markdown/item) and not(content/markdown/@publish)">
<p><input type="submit" value="{/doc/i18n/admin/publish}" style="width: 100%;" name="publish"/></p>
</xsl:if>
</xsl:if>
<p><input type="submit" value="{/doc/i18n/admin/logout}" style="width: 100%;" name="logout"/></p>
</form>
</xsl:template>




<xsl:template match="markdown[@edit]" mode="content">
<form action="" method="POST">
<input type="hidden" name="markdown" value="{@file}"/>
<textarea name="text" style="height: 30em;"><xsl:value-of select="@edit"/></textarea>
<p><input type="submit" value="{/doc/i18n/admin/save}" name="markdown_save"/></p>
</form>
</xsl:template>

<xsl:template match="create" mode="content">
<form action="" method="POST">
<input type="hidden" name="create_file" value="1"/>
<p><label for="markdown"><xsl:value-of select="/doc/i18n/admin/filename"/>:</label>
<input id="markdown" type="text" style="width: 95%;" name="markdown"/></p>
<p><input type="submit" value="{/doc/i18n/admin/create}" name="edit"/></p>
</form>
</xsl:template>

<xsl:template match="markdown[@publish]" mode="content">
<form action="{/doc/@root}" method="POST">
<input type="hidden" name="link" value="{@file}"/>
<p><label for="title"><xsl:value-of select="/doc/i18n/admin/rss_title"/>:</label>
<input id="title" type="text" style="width: 95%;" name="title" value="{parent::content/@title}"/></p>
<p><label for="description"><xsl:value-of select="/doc/i18n/admin/rss_description"/>:</label>
<textarea id="description" name="description"><xsl:value-of select="@publish"/></textarea></p>
<p><label for="tags"><xsl:value-of select="/doc/i18n/admin/rss_tags"/>:</label>
<input id="tags" type="text" name="tags" style="width: 95%;" value=""/></p>
<p><input type="submit" value="{/doc/i18n/admin/publish}" name="publish_save"/></p>
</form>
</xsl:template>

</xsl:stylesheet>
