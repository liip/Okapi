<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    xmlns:exslt="http://exslt.org/common"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    version="1.0">
    
    <xsl:output encoding="UTF-8" method="xml" />

    <xsl:template match="*" mode="forms">
        <xsl:param name="form" required="yes"/>
        <xsl:param name="formName" required="yes"/>
        <xsl:copy select=".">
            <xsl:copy-of select="@*"/>
        
            <xsl:apply-templates mode="forms">
                <xsl:with-param name="form" select="$form"/>
                <xsl:with-param name="formName" select="$formName"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="text()" mode="forms"><xsl:copy-of select="." /></xsl:template>

    <xsl:template match="xhtml:form" mode="forms">
        <xsl:param name="form" required="yes"/>
        <xsl:param name="formName" required="yes"/>
    
        <xsl:variable name="thisForm" select="$form/child::*[name()=$formName]" />
        <xsl:variable name="ERRORS" select="$thisForm/ERRORS/*" />
    
        <xsl:copy select=".">
            <xsl:copy-of select="@*"/>
            <xsl:if test="not(@action) and string-length($queryinfo/self) &gt; 0">
                <xsl:attribute name="action"><xsl:value-of select="$queryinfo/self" /></xsl:attribute>
            </xsl:if>
        
            <xsl:attribute name="name"><xsl:value-of select="$formName"/></xsl:attribute>
            <xsl:attribute name="method">post</xsl:attribute>
            
            <!-- Submit the name of this form -->
            <input type="hidden" name="f__name" value="{$formName}" />
            
            <!-- Print a list of global formular errors -->
            <xsl:if test="boolean($ERRORS)">
                <ul class="formErrorList">
                <xsl:for-each select="$ERRORS">
                   <li><i18n:text><xsl:value-of select="." /></i18n:text></li>
                </xsl:for-each>
                </ul>    
            </xsl:if>
            
            <!-- generate form content -->
            <xsl:apply-templates select="./*" mode="forms">
                <xsl:with-param name="form" select="$thisForm"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="xhtml:textarea|xhtml:input" mode="forms">
        <xsl:param name="form"/>
       
        <xsl:variable name="nameAttribute" select="@name" />
        <xsl:variable name="valueAttribute" select="@value" />
    
        <xsl:variable name="name">f_<xsl:value-of select="$nameAttribute" /><xsl:if test="@type='checkbox'">[<xsl:value-of select="$valueAttribute" />]</xsl:if></xsl:variable>
        <xsl:variable name="node" select="$form/child::*[name()=$name]" />
        <xsl:variable name="nodeCb" select="$form/child::*[name()=concat('f_',$nameAttribute)]" />
        <xsl:variable name="nodeValue" select="$node/values" />
        <xsl:variable name="nodeValueCb" select="$nodeCb/values" />
        <xsl:variable name="isNode" select="boolean($node)" />
        <xsl:variable name="isNodeCb" select="boolean($nodeCb)" />
        <xsl:variable name="isValueFromAttribute" select="
                (@type!='checkbox' and (not($isNode) or @type='radio'))
                or @type='submit' or @type='button' or @type='reset'" />
        <xsl:variable name="isChecked" select=
            "( @type='radio' and ( ($isNode and $nodeValue=@value) or ( not($isNode) and @checked='checked') ) )
            or
            ( @type='checkbox' and ( ($isNodeCb and $nodeValueCb='1' or $nodeValueCb/entry='1') or (not($isNodeCb) and @checked='checked') ) )
            " />

        <xsl:variable name="isError">
            <xsl:choose>
                <xsl:when test="@type='checkbox' and boolean($nodeCb/errors/child::*[name()=$valueAttribute])">Error</xsl:when>
                <xsl:when test="boolean($node/errors/*)">Error</xsl:when>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="id">
            <xsl:choose>
                <xsl:when test="@id != ''"><xsl:value-of select="@id" /></xsl:when>
                <xsl:when test="$name != 'f_' and @type='radio'">
                    <xsl:value-of select="concat($name, '_', @value)" />
                </xsl:when>
                <xsl:when test="@type='checkbox'">f_<xsl:value-of select="$nameAttribute" />_<xsl:value-of select="$valueAttribute" /></xsl:when>
                <xsl:when test="$name != 'f_'"><xsl:value-of select="$name" /></xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
    
        <xsl:variable name="class">
            <xsl:call-template name="formAttributeClass">
                <xsl:with-param name="name" select="name(.)" />
                <xsl:with-param name="class" select="@class" />
                <xsl:with-param name="type" select="@type" />
                <xsl:with-param name="isError" select="$isError" />
            </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="value">
                <xsl:choose>
                    <xsl:when test="@type='checkbox'">1</xsl:when>
                    <xsl:when test="not($isValueFromAttribute)"><xsl:value-of select="$nodeValue" /></xsl:when>
                </xsl:choose>
        </xsl:variable>


        <!-- Assemble the element -->
        <xsl:variable name="this">
            <xsl:copy select=".">
                <xsl:for-each select="@*">
                    <xsl:choose>
                        <xsl:when test="name(.)='name'"><xsl:attribute name="name"><xsl:value-of select="$name"/></xsl:attribute></xsl:when>
                        <xsl:when test="
                        name(.)='labelBefore' or name(.)='labelAfter'
                        or (name(.)='value' and not($isValueFromAttribute))
                        or (name(.)='checked' and not($isChecked))
                        or (name(.)='class')
                        or (name(.)='id')
                        "></xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
                <!--
                    Define the attributes here because if they are missing in the element,
                    they can't be processed with the for-each loop ;-)
                -->
                <xsl:if test="$isChecked"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                <xsl:attribute name="class"><xsl:value-of select="$class"/></xsl:attribute>
                <xsl:attribute name="id"><xsl:value-of select="$id"/></xsl:attribute>
                <xsl:choose>
                    <!-- set content of textarea -->
                    <xsl:when test="name(.)='textarea' and string-length($value) = 0"><xsl:copy-of select="node()" /></xsl:when>
                    <xsl:when test="name(.)='textarea'"><xsl:copy-of select="$value" /></xsl:when>
                    <!-- set value attribute for other elements -->
                    <xsl:when test="$value!=''"><xsl:attribute name="value"><xsl:value-of select="$value" /></xsl:attribute></xsl:when>
                </xsl:choose>
            </xsl:copy>
        </xsl:variable>

        <!-- Create label for all elements, but buttons -->
        <xsl:choose>
            <xsl:when test="@type!='submit' and @type!='button' and @type!='reset'">
                <xsl:call-template name="formLabel">
                    <xsl:with-param name="id" select="$id" />
                    <xsl:with-param name="labelBefore" select="@labelBefore" />
                    <xsl:with-param name="labelAfter" select="@labelAfter" />
                    <xsl:with-param name="this" select="$this" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise><xsl:copy-of select="$this" /></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="xhtml:select" mode="forms">
        <xsl:param name="form"/>
       
        <xsl:variable name="nameAttribute" select="@name" />
        <xsl:variable name="name">f_<xsl:value-of select="$nameAttribute" /></xsl:variable>
        <xsl:variable name="node" select="$form/child::*[name()=$name]" />
        <xsl:variable name="nodeValue" select="$node/values" />
        <xsl:variable name="isError" select="boolean($node/errors/*)" />

        <xsl:variable name="id">
            <xsl:choose>
                <xsl:when test="@id != ''"><xsl:value-of select="@id" /></xsl:when>
                <xsl:when test="$name != 'f_'"><xsl:value-of select="$name" /></xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- Assemble the element -->
        <xsl:variable name="this">
            <xsl:copy select=".">
                <xsl:for-each select="@*">
                    <xsl:choose>
                        <xsl:when test="name(.)='name'"><xsl:attribute name="name"><xsl:value-of select="$name"/></xsl:attribute></xsl:when>
                        <xsl:when test="
                            name(.)='labelBefore' or name(.)='labelAfter'
                            or (name(.)='class')
                            or (name(.)='id')
                        "></xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
                <!--
                    Define the attributes here because if they are missing in the element,
                    they can't be processed with the for-each loop ;-)
                -->
                <xsl:attribute name="class">
                    <xsl:call-template name="formAttributeClass">
                        <xsl:with-param name="name" select="name(.)" />
                        <xsl:with-param name="class" select="@class" />
                        <xsl:with-param name="isError" select="$isError" />
                    </xsl:call-template>
                </xsl:attribute>
                <xsl:attribute name="id"><xsl:value-of select="$id"/></xsl:attribute>
            
                <xsl:for-each select="xhtml:option">
                    <xsl:copy select=".">
                        <xsl:if test="$nodeValue = @value or (not($nodeValue) and @selected != '')">
                            <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute>
                    
                        <xsl:copy-of select="*|text()" />
                    </xsl:copy>
                </xsl:for-each>
            </xsl:copy>
        </xsl:variable>

        <!-- Create label for all elements, but buttons -->
        <xsl:call-template name="formLabel">
            <xsl:with-param name="id" select="$id" />
            <xsl:with-param name="labelBefore" select="@labelBefore" />
            <xsl:with-param name="labelAfter" select="@labelAfter" />
            <xsl:with-param name="this" select="$this" />
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="xhtml:label" mode="forms">
        <xsl:param name="form" required="yes"/>
        <xsl:param name="formName" required="yes"/>
        
        <xsl:copy select=".">
            <xsl:if test="@for">
                <xsl:attribute name="for">
                    <xsl:value-of select="concat('f_', @for)" />
                </xsl:attribute>
            </xsl:if>
            <xsl:copy-of select="@*[name() != 'for']"/>
        
            <xsl:apply-templates mode="forms">
                <xsl:with-param name="form" select="$form"/>
                <xsl:with-param name="formName" select="$formName"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>

    <!-- Generate an i18n translated label! -->
    <xsl:template name="formLabel">
        <xsl:param name="id" />
        <xsl:param name="labelBefore" />
        <xsl:param name="labelAfter" />
        <xsl:param name="this" />
    
        <xsl:variable name="label">
            <xsl:choose>
                <xsl:when test="$labelBefore != ''"><xsl:value-of select="$labelBefore" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="$labelAfter" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
    
        <xsl:variable name="labelTag">
            <label for="{$id}"><i18n:text><xsl:value-of select="$label" /></i18n:text></label>
        </xsl:variable>
    
        <xsl:choose>
            <xsl:when test="$labelBefore != ''"><xsl:copy-of select="$labelTag" /><xsl:copy-of select="$this" /></xsl:when>
            <xsl:when test="$labelAfter != ''"><xsl:copy-of select="$this" /><xsl:copy-of select="$labelTag" /></xsl:when>
            <xsl:otherwise><xsl:copy-of select="$this" /></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Generate the classname of an element.
        The classname is composed of the elementname or element-type (for input element's) and
        is prepended to the value of the user-supplied class attribute.
     -->
    <xsl:template name="formAttributeClass">
        <xsl:param name="name" /><xsl:param name="type" /><xsl:param name="class" /><xsl:param name="isError" />
    
        <xsl:variable name="default">
            <xsl:choose>
                <xsl:when test="$name='input' and ($type='submit' or $type='reset')"><xsl:value-of select="concat($type,' button')" /></xsl:when>
                <xsl:when test="$name='input'"><xsl:value-of select="$type" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="$name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
    
        <xsl:variable name="error"><xsl:if test="$isError='Error'"> error</xsl:if></xsl:variable>
    
        <xsl:value-of select="concat($default,' ',$error,' ',@class)" />
    </xsl:template>
</xsl:stylesheet>
