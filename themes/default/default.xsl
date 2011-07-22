<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns="http://www.w3.org/1999/xhtml" xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    exclude-result-prefixes="xhtml i18n" version="1.0">

    <xsl:template match="/">
        <html lang="{$lang}" xml:lang="{$lang}">
            <head>
                <meta http-equiv="content-type" content="text/html; charset=utf-8" />
                <meta name="robots" content="NONE,NOARCHIVE" />
                <title>
                    <i18n:text>PageTitle</i18n:text>
                </title>
                <link rel="stylesheet" type="text/css"
                    href="{concat($webrootStatic, 'stylesheets/screen.css')}" />
            </head>

            <body>
                <div id="summary">
                    <h1>
                        <i18n:text>WelcomeText</i18n:text>
                    </h1>
                    <h2>
                        <i18n:text>Congratulations</i18n:text>
                    </h2>
                </div>

                <div id="instructions">
                    <p>
                        <i18n:text>Instructions</i18n:text>
                    </p>
                    <ul>
                        <li>
                            <i18n:text>InstructionsStep1</i18n:text>
                        </li>
                        <li>
                            <i18n:text>InstructionsStep2</i18n:text>
                        </li>
                        <li>
                            <i18n:text>InstructionsStep3</i18n:text>
                        </li>
                    </ul>
                </div>

                <div id="explanation">
                    <p>
                        <i18n:text>Explanation</i18n:text>
                    </p>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
