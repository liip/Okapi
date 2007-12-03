<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns="http://www.w3.org/1999/xhtml" 
        xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
        version="1.0"
        exclude-result-prefixes="xhtml i18n">
        
    <xsl:param name="webroot" select="'webroot'"/>
    <xsl:param name="webrootStatic" select="'webrootStatic'"/>
    <xsl:param name="projectDir" select="'projectDir'"/>
    
    <xsl:variable name="method" select="/response/command/response/@method"/>
    <xsl:variable name="requestUri" select="/response/command/xhtml:html/queryinfo/requestURI"/>
    
    <xsl:output type="xml"
            doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
            doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" encoding="utf-8"/>
    
    <xsl:template match="/">
        <html>
            <head>
                <title>
                    <i18n:text>PageTitle</i18n:text>
                </title>
                <link rel="stylesheet" type="text/css" href="{$webrootStatic}css/main.css"/>
                <link rel="shortcut icon" href="/favicon.ico"/>
                <xsl:call-template name="addtohead"/>
            </head>
            <body>
                <xsl:call-template name="content"/>
            </body>
        </html>
    </xsl:template>
    
    <xsl:template name="addtohead"/>
    <xsl:template name="content"/>
</xsl:stylesheet>
