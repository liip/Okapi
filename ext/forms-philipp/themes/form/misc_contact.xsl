<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns="http://www.w3.org/1999/xhtml" 
        xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
        exclude-result-prefixes="xhtml i18n"
        version="1.0">

    <xsl:import href="../master.xsl"/>
    <xsl:import href="../common_forms.xsl"/>

    <xsl:template name="maincontainer">
		<div id="maincontainer">

            <h1 class="cmsform"><i18n:text>Contact</i18n:text></h1>
            <p class="cmsform"><i18n:text i18n:key="msg_form_misc_contact"/></p>
            
            <form id="contact" action="." method="post" name="contact">

                <xsl:apply-templates select="/command[@name='form']/form" mode="form"/>

                <fieldset>
                    <xsl:apply-templates select="/command[@name='form']/form/sections/section" mode="form"/>
    
                    <p>
                        <input class="button" type="submit" i18n:attr="value">
                            <xsl:attribute name="value">
                                <i18n:text>Send</i18n:text>
                            </xsl:attribute>
                        </input>
                    </p>
                    
                </fieldset>
            </form>
        </div>
    </xsl:template>

</xsl:stylesheet>
