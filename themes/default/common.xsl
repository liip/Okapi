<?xml version="1.0"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns="http://www.w3.org/1999/xhtml"
        xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
        exclude-result-prefixes="i18n"
        version="1.0">
    
    <xsl:output encoding="utf-8" method="xml"/>
    
    <xsl:template match="/|comment()|processing-instruction()" mode="xhtml">
        <xsl:copy>
            <xsl:apply-templates mode="xhtml"/>
        </xsl:copy>
    </xsl:template>
    
    <!-- translate links from filename.lang.ext to filename.html -->
    <xsl:template match="*[local-name()='a']" mode="xhtml">
        <xsl:element name="a">
            <xsl:if test="@href">
                <!-- FIXME: do we really need that? -->
                <xsl:attribute name="href">
                    <xsl:value-of select="@href"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates select="@*[not(local-name()='href')]" mode="xhtml"/>
            <xsl:apply-templates select="node()" mode="xhtml"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="*" mode="xhtml">
        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:apply-templates mode="xhtml"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="*[namespace-uri() = 'http://apache.org/cocoon/i18n/2.1']" mode="xhtml">
        <xsl:copy>
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:apply-templates mode="xhtml"/>
        </xsl:copy>
    </xsl:template>
    
    <xsl:template match="@*" mode="xhtml">
        <xsl:copy-of select="."/>
    </xsl:template>
    
    <!-- 
    add empty alt attribute when not set - 
    xhtml validity issue ...
    -->
    <xsl:template match="*[local-name()='img']" mode="xhtml">
        <xsl:element name="img">
            <xsl:apply-templates select="@*" mode="xhtml"/>
            <xsl:if test="not(@alt)">
                <xsl:attribute name="alt">
                    <xsl:value-of select="@src"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates select="node()" mode="xhtml"/>
        </xsl:element>
    </xsl:template>
    
    <!-- kupu bockmist ;) -->
    <xsl:template match="xhtml:br/@type" mode="xhtml">
    </xsl:template>
</xsl:stylesheet>
