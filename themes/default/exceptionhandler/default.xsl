<?xml version="1.0"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
        xmlns="http://www.w3.org/1999/xhtml"
        exclude-result-prefixes="xhtml"
        version="1.0">
    
    <xsl:import href="../master.xsl" />
    <xsl:import href="../common.xsl" />

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
    
    <xsl:variable name="exception" select="/response/command[@name='exception']"/>
    
    <xsl:template name="addtohead">
        <link rel="stylesheet" type="text/css" href="{$webrootStatic}css/exceptionhandler.css"/>
    </xsl:template>

    <xsl:template name="content">
        <div id="content">
            <h1><xsl:value-of select="$exception/name"/>&#xA0;<i18n:text>Exception</i18n:text></h1>
            <p>
                <i18n:text>With message:</i18n:text>
            </p>
            <h2>
                <xsl:value-of select="$exception/message"/>
            </h2>
            <p>
                <i18n:text>Thrown at:</i18n:text>&#xA0;<xsl:value-of select="$exception/file"/>&#xA0;(<xsl:value-of select="$exception/line"/>)
	        </p>
	        
            <table border="0" cellpadding="0" cellspacing="0">
                <thead>
                    <th/>
                    <th>Class/Method</th>
                    <th>File</th>
                    <th>Line</th>
                </thead>
                <tbody>
                    <xsl:for-each select="$exception/backtrace/entry">
                        <xsl:variable name="caller" select="caller"/>
                        <tr>
                            <xsl:attribute name="class">grey</xsl:attribute>
                            <td/>
                            <td>
                                <xsl:if test="not(class='')">
                                    <xsl:value-of select="class"/>
                                    <xsl:text>::</xsl:text>
                                </xsl:if>
                                <xsl:value-of select="function"/>
                            </td>
                            <td>
                                <xsl:value-of select="substring-after(file,$projectDir)"/>
                            </td>
                            <td>
                                <xsl:value-of select="line"/>
                            </td>
                        </tr>
                        
                        <xsl:if test="count(source/entry) &gt; 0">
                            <tr class="codetr">
                                <td colspan="4">
                                    <div class="codeblock">
                                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                            <xsl:for-each select="source/entry">
                                                <tr>
                                                    <td width="40">
                                                        <xsl:value-of select="@key"/>
                                                    </td>
                                                    <td>
                                                        <xsl:if test="not($caller='') and $caller=@key">
                                                            <xsl:attribute name="class">red</xsl:attribute>
                                                        </xsl:if>
                                                        <pre>
                                                            <xsl:value-of select="." disable-output-escaping="yes"/>
                                                        </pre>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </xsl:if>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
    </xsl:template>
    
    <xsl:template match="xhtml:h1" mode="xhtml">
    </xsl:template>
    
    <!-- add everything from head to the output -->
    <xsl:template name="html_head">
    </xsl:template>
    
    <!-- except the title -->
    <xsl:template match="xhtml:head/xhtml:title" mode="xhtml">
    </xsl:template>
    
    <!-- except the links -->
    <xsl:template match="xhtml:head/xhtml:link" mode="xhtml">
    </xsl:template>
    
    <!-- do not output meta tags without @content -->
    <xsl:template match="xhtml:head/xhtml:meta[not(@content)]" mode="xhtml">
    </xsl:template>
    
    <xsl:template name="body_attributes">
    </xsl:template>
</xsl:stylesheet>
