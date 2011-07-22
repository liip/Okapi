<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang}">
            <head>
                <xsl:apply-templates select="openid_discovery/openid" />
            </head>
            <body></body>
        </html>
    </xsl:template>
    
    <xsl:template match="openid" >
        <link rel="{server/rel}" href="{server/@href}" />
    </xsl:template>
    
</xsl:stylesheet>