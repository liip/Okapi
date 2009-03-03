<?xml version="1.0"?>
<xsl:stylesheet xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">

    <xsl:output encoding="utf-8" method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
        doctype-public="-//W3C//DTD XHTML 1.1//EN" />

    <xsl:template match="/command/exception">
        <html xml:lang="en">
            <xsl:attribute name="xmlns">http://www.w3.org/1999/xhtml</xsl:attribute>
            <head>
                <meta http-equiv="content-type" content="text/html; charset=utf-8" />
                <meta name="robots" content="NONE,NOARCHIVE" />
                <title>
                    <xsl:value-of select="name" />
                    <xsl:text> - Okapi default error page</xsl:text>
                </title>
                <link rel="stylesheet" type="text/css" href="{concat($webrootStatic, 'stylesheets/error.css')}" />

            </head>
            <body>
                <div id="summary">
                    <h1>
                        <xsl:value-of select="name" />
                    </h1>
                    <pre class="exception_value">
                        <xsl:value-of select="message" />
                    </pre>
                    <table class="meta">
                        <tr>
                            <th>
                                <xsl:text>Exception Location:</xsl:text>
                            </th>
                            <td>
                                <xsl:value-of select="file" />
                                <xsl:text>, line </xsl:text>
                                <xsl:value-of select="line" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <xsl:text>Theme:</xsl:text>
                            </th>
                            <td>
                                <xsl:value-of select="$theme" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <xsl:text>Language:</xsl:text>
                            </th>
                            <td>
                                <xsl:value-of select="$lang" />
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="traceback">
                    <h2>
                        <xsl:text>Traceback</xsl:text>
                    </h2>

                    <div id="browserTraceback">
                        <ul class="traceback">
                            <xsl:apply-templates select="backtrace/entry" />
                        </ul>
                    </div>
                </div>

                <div id="requestinfo">
                    <h2>
                        <xsl:text>Request information</xsl:text>
                    </h2>

                    <h3 id="get-info">
                        <xsl:text>Settings</xsl:text>
                    </h3>
                    <table class="req">
                        <thead>
                            <tr>
                                <th>
                                    <xsl:text>Variable</xsl:text>
                                </th>
                                <th>
                                    <xsl:text>Value</xsl:text>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    <xsl:text>API_WEBROOT:</xsl:text>
                                </th>
                                <td class="code">
                                    <div>
                                        <xsl:value-of select="$webroot" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <xsl:text>API_WEBROOT_STATIC:</xsl:text>
                                </th>
                                <td class="code">
                                    <div>
                                        <xsl:value-of select="$webrootStatic" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <xsl:text>API_MOUNTPATH:</xsl:text>
                                </th>
                                <td class="code">
                                    <div>
                                        <xsl:value-of select="$mountpath" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <xsl:text>API_PROJECT_DIR:</xsl:text>
                                </th>
                                <td class="code">
                                    <div>
                                        <xsl:value-of select="$projectDir" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="explanation">
                    <p>
                        <xsl:text>Okapi default error page. Please don't use in production - it won't make clients happy.</xsl:text>
                    </p>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="backtrace/entry">
        <li class="frame">
            <code>
                <xsl:value-of select="substring-after(file,$projectDir)" />
            </code>
            <xsl:text>, line </xsl:text>
            <code>
                <xsl:value-of select="caller + 1" />
            </code>
            <xsl:text> in </xsl:text>
            <code>
                <xsl:if test="class != ''">
                    <xsl:value-of select="class" />
                    <xsl:text>::</xsl:text>
                </xsl:if>
                <xsl:value-of select="function" />
            </code>

            <xsl:if test="source/entry">
                <div class="context">
                    <ol class="pre-context" start="{source/entry/@key + 1}">
                        <xsl:apply-templates select="source/entry" />
                    </ol>
                </div>
            </xsl:if>
        </li>
    </xsl:template>

    <xsl:template match="backtrace/entry/source/entry">
        <xsl:variable name="caller" select="../../caller" />

        <li>
            <xsl:if test="$caller != '' and @key=$caller">
                <xsl:attribute name="class">caller</xsl:attribute>
            </xsl:if>
            <pre>
                <xsl:call-template name="filter-line-breaks">
                    <xsl:with-param name="text" select="." />
                </xsl:call-template>
                <xsl:text>&#160;</xsl:text>
            </pre>
        </li>
    </xsl:template>

    <xsl:template name="filter-line-breaks">
        <xsl:param name="text" />
        <xsl:choose>
            <xsl:when test="contains($text, '&#13;')">
                <xsl:value-of select="substring-before($text, '&#13;')" />
                <xsl:call-template name="filter-line-breaks">
                    <xsl:with-param name="text" select="substring-after($text, '&#13;')" />
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains($text, '&#10;')">
                <xsl:value-of select="substring-before($text, '&#10;')" />
                <xsl:call-template name="filter-line-breaks">
                    <xsl:with-param name="text" select="substring-after($text, '&#10;')" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$text" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
