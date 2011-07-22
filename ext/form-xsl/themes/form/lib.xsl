<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1" exclude-result-prefixes="i18n" version="1.0">

    <xsl:template name="form_fieldset">
        <xsl:param name="title" />
        <xsl:param name="legend" />
        <xsl:param name="class" />
        <xsl:param name="elements" />
        <fieldset class="{$class}">
            <xsl:if test="$title != ''">
                <h3>
                    <i18n:text>
                        <xsl:copy-of select="$title" />
                    </i18n:text>
                </h3>
            </xsl:if>
            <xsl:if test="$legend != ''">
                <legend>
                    <i18n:text>
                        <xsl:copy-of select="$legend" />
                    </i18n:text>
                </legend>
            </xsl:if>
            <xsl:copy-of select="$elements" />
        </fieldset>
    </xsl:template>

    <xsl:template name="form_input">
        <xsl:param name="id" />
        <xsl:param name="name" select="$id" />
        <xsl:param name="label" />
        <xsl:param name="type" select="'text'" />
        <xsl:param name="size" select="32" />
        <xsl:param name="value" select="/command/form_values/*[name()=$name]" />
        <xsl:param name="required" select="false()" />
        <xsl:param name="error" select="/command/error/*[name()=$name]" />
        <xsl:param name="class" select="''" />
        <xsl:param name="label_tag">
            <xsl:call-template name="form_label_tag">
                <xsl:with-param name="for" select="$id" />
                <xsl:with-param name="label" select="$label" />
                <xsl:with-param name="required" select="$required" />
            </xsl:call-template>
        </xsl:param>
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text </xsl:text>
                <xsl:if test="$error">
                    <xsl:text>error </xsl:text>
                </xsl:if>
                <xsl:value-of select="$class" />
            </xsl:attribute>
            <xsl:copy-of select="$label_tag" />
            <input type="{$type}" id="{$id}" name="{$name}" value="{$value}" size="{$size}" />
            <xsl:call-template name="form_description">
                <xsl:with-param name="message" select="$error" />
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template name="form_input_readonly">
        <xsl:param name="id" />
        <xsl:param name="name" select="$id" />
        <xsl:param name="label" />
        <xsl:param name="size" select="32" />
        <xsl:param name="value" select="/command/form_values/*[name()=$name]" />
        <xsl:param name="required" select="false()" />
        <xsl:param name="error" select="/command/error/*[name()=$name]" />
        <xsl:param name="class" />
        <xsl:param name="label_tag">
            <xsl:call-template name="form_label_tag">
                <xsl:with-param name="for" select="$id" />
                <xsl:with-param name="label" select="$label" />
                <xsl:with-param name="required" select="$required" />
            </xsl:call-template>
        </xsl:param>
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text </xsl:text>
                <xsl:if test="$error">
                    <xsl:text>error </xsl:text>
                </xsl:if>
                <xsl:value-of select="$class" />
            </xsl:attribute>
            <xsl:copy-of select="$label_tag" />
            <input type="text" id="{$id}" name="{$name}" value="{$value}" size="{$size}"
                readonly="" />
            <xsl:call-template name="form_description">
                <xsl:with-param name="message" select="$error" />
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template name="form_textarea">
        <xsl:param name="id" />
        <xsl:param name="name" select="$id" />
        <xsl:param name="label" />
        <xsl:param name="value" select="/command/form_values/*[name()=$name]" />
        <xsl:param name="rows" select="6" />
        <xsl:param name="cols" select="30" />
        <xsl:param name="required" select="false()" />
        <xsl:param name="error" select="/command/error/*[name()=$name]" />
        <xsl:param name="class" />
        <xsl:param name="label_tag">
            <xsl:call-template name="form_label_tag">
                <xsl:with-param name="for" select="$id" />
                <xsl:with-param name="label" select="$label" />
                <xsl:with-param name="required" select="$required" />
            </xsl:call-template>
        </xsl:param>
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text </xsl:text>
                <xsl:if test="$error">
                    <xsl:text>error </xsl:text>
                </xsl:if>
                <xsl:value-of select="$class" />
            </xsl:attribute>
            <xsl:copy-of select="$label_tag" />
            <textarea type="text" id="{$id}" name="{$name}" rows="{$rows}" cols="{$cols}">
                <xsl:value-of select="$value" />
            </textarea>
            <xsl:call-template name="form_description">
                <xsl:with-param name="message" select="$error" />
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template name="form_select">
        <xsl:param name="id" />
        <xsl:param name="name" select="$id" />
        <xsl:param name="label" />
        <xsl:param name="options">
            <option value="" />
        </xsl:param>
        <xsl:param name="required" select="false()" />
        <xsl:param name="error" select="/command/error/*[name()=$name]" />
        <xsl:param name="class" />
        <xsl:param name="label_tag">
            <xsl:call-template name="form_label_tag">
                <xsl:with-param name="for" select="$id" />
                <xsl:with-param name="label" select="$label" />
                <xsl:with-param name="required" select="$required" />
            </xsl:call-template>
        </xsl:param>
        <p>
            <xsl:attribute name="class">
                <xsl:text>input select </xsl:text>
                <xsl:if test="$error">
                    <xsl:text>error </xsl:text>
                </xsl:if>
                <xsl:value-of select="$class" />
            </xsl:attribute>
            <xsl:copy-of select="$label_tag" />
            <xsl:text> </xsl:text>
            <select id="{$id}" name="{$name}">
                <xsl:copy-of select="$options" />
            </select>
            <xsl:call-template name="form_description">
                <xsl:with-param name="message" select="$error" />
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template name="form_radio">
        <xsl:param name="id" />
        <xsl:param name="name" select="$id" />
        <xsl:param name="label" />
        <xsl:param name="value" select="/command/form_values/*[name()=$name]" />
        <xsl:param name="options" />
        <xsl:param name="inputs">
            <xsl:for-each select="$options">
                <input type="radio" id="{$id}[{@key}]" name="{$name}" value="{@key}">
                    <xsl:if test="@key = $value or (@key = '' and not($value))">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="{$id}[{@key}]" class="inline">
                    <xsl:value-of select="." />
                </label>
                <xsl:text> </xsl:text>
            </xsl:for-each>
        </xsl:param>
        <xsl:param name="required" select="false()" />
        <xsl:param name="error" select="/command/error/*[name()=$name]" />
        <xsl:param name="class" />
        <xsl:param name="label_tag">
            <span class="title">
                <xsl:if test="$label != ''">
                    <i18n:text>
                        <xsl:value-of select="$label" />
                    </i18n:text>
                    <xsl:if test="$required">
                        <xsl:text> </xsl:text>
                        <span class="required">*</span>
                    </xsl:if>
                    <xsl:text>:</xsl:text>
                </xsl:if>
            </span>
        </xsl:param>

        <p>
            <xsl:attribute name="class">
                <xsl:text>input radio </xsl:text>
                <xsl:if test="$error">
                    <xsl:text>error </xsl:text>
                </xsl:if>
                <xsl:value-of select="$class" />
            </xsl:attribute>
            <xsl:if test="$label_tag != ''">
                <xsl:copy-of select="$label_tag" />
            </xsl:if>
            <xsl:copy-of select="$inputs" />

            <xsl:call-template name="form_description">
                <xsl:with-param name="message" select="$error" />
            </xsl:call-template>
        </p>
    </xsl:template>

    <xsl:template name="form_label_tag">
        <xsl:param name="for" />
        <xsl:param name="label" />
        <xsl:param name="required" select="false()" />
        <label for="{$for}" class="title">
            <xsl:if test="$label != ''">
                <i18n:text>
                    <xsl:value-of select="$label" />
                </i18n:text>
                <xsl:if test="$required">
                    <xsl:text> </xsl:text>
                    <span class="required">*</span>
                </xsl:if>
                <xsl:text>:</xsl:text>
            </xsl:if>
        </label>
    </xsl:template>
    <xsl:template name="form_description">
        <xsl:param name="message" />
        <span class="message">
            <xsl:if test="$message != ''">
                <i18n:text>
                    <xsl:value-of select="$message" />
                </i18n:text>
            </xsl:if>
        </span>
    </xsl:template>

    <xsl:template match="form">
        <xsl:apply-templates select="group" />
    </xsl:template>

    <xsl:template match="group">
        <fieldset>
            <xsl:if test="position() mod 2 = 0">
                <xsl:attribute name="class">
                    <xsl:text>odd</xsl:text>
                    <xsl:apply-templates select="attrib[@key = 'class']" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="position() = 1">
                <xsl:attribute name="class">
                    <xsl:text>first</xsl:text>
                    <xsl:apply-templates select="attrib[@key = 'class']" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@description != ''">
                <h3>
                    <xsl:value-of select="@description" />
                </h3>
            </xsl:if>
            <xsl:apply-templates select="element" />
        </fieldset>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_Text']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text</xsl:text>
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <xsl:apply-templates select="@label" />
            <input type="text" id="{@id}" name="{@name}" value="{@value}" />
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_Captcha']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text captcha</xsl:text>
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <label for="{@id}-input" class="title">
                <xsl:if test="@label != ''">
                    <i18n:text>
                        <xsl:value-of select="@label" />
                    </i18n:text>
                    <xsl:if test="@required = 'true'">
                        <xsl:text> </xsl:text>
                        <span class="required">*</span>
                    </xsl:if>
                    <xsl:text>:</xsl:text>
                </xsl:if>
            </label>
            <span class="inputs">
                <label for="{@id}-input">
                    <img src="{@img}" alt="" />
                </label>
                <input type="hidden" id="{@id}-id" name="{@name}[id]" value="{@captcha}"
                    class="hidden" />
                <input type="text" id="{@id}-input" name="{@name}[input]" value="" />
            </span>
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_Textarea']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text</xsl:text>
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <xsl:apply-templates select="@label" />
            <textarea id="{@id}" name="{@name}" rows="3">
                <xsl:value-of select="@value" />
            </textarea>
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_Select']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input select</xsl:text>
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <xsl:apply-templates select="@label" />
            <select id="{@id}" name="{@name}">
                <xsl:for-each select="option">
                    <option value="{@key}">
                        <xsl:if test="../@value = @key">
                            <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:if test=". != ''">
                            <i18n:text>
                                <xsl:value-of select="." />
                            </i18n:text>
                        </xsl:if>
                    </option>
                </xsl:for-each>
            </select>
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_MultiCheckbox']">
        <div>
            <xsl:attribute name="class">
                <xsl:text>input check</xsl:text>
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <span class="title">
                <xsl:if test=". != ''">
                    <i18n:text>
                        <xsl:value-of select="@label" />
                    </i18n:text>
                    <xsl:if test="@required = 'true'">
                        <xsl:text> </xsl:text>
                        <span class="required">*</span>
                    </xsl:if>
                    <xsl:text>:</xsl:text>
                </xsl:if>
            </span>
            <ul>
                <xsl:for-each select="option">
                    <xsl:variable name="key" select="@key" />
                    <li>
                        <input type="checkbox" id="{../@id}-{@key}" name="{../@id}[]" value="{@key}">
                            <xsl:if test="../value[text() = $key]">
                                <xsl:attribute name="checked">checked</xsl:attribute>
                            </xsl:if>
                        </input>
                        <label for="{../@id}-{@key}" class="description">
                            <i18n:text>
                                <xsl:value-of select="." />
                            </i18n:text>
                        </label>
                    </li>
                </xsl:for-each>
            </ul>
        </div>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_Password']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text</xsl:text>
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <xsl:apply-templates select="@label" />
            <input type="password" id="{@id}" name="{@name}" value="{@value}" />
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_File']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text file</xsl:text>
                <xsl:if test="@error = 'true'"> error</xsl:if>
            </xsl:attribute>
            <xsl:apply-templates select="@label" />
            <input type="file" id="{@id}" name="{@name}" />
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="element[@type = 'Zend_Form_Element_Text' and @array = 'true']">
        <p>
            <xsl:attribute name="class">
                <xsl:text>input text two </xsl:text>
                <xsl:value-of select="@id" />
                <xsl:if test="@error = 'true'">
                    <xsl:text> error</xsl:text>
                </xsl:if>
                <xsl:apply-templates select="attrib[@key = 'class']" />
            </xsl:attribute>
            <xsl:apply-templates select="@label" />
            <input type="text" id="{@id}[0]" name="{@name}[0]" size="4" class="small"
                value="{value[@key = 0]}" />
            <input type="text" id="{@id}[1]" name="{@name}[1]" value="{value[@key = 1]}" />
            <xsl:apply-templates select="@description" />
        </p>
    </xsl:template>

    <xsl:template match="attrib[@key = 'class']">
        <xsl:text> </xsl:text>
        <xsl:value-of select="." />
    </xsl:template>

    <xsl:template match="@label">
        <label for="{../@id}" class="title">
            <xsl:if test=". != ''">
                <i18n:text>
                    <xsl:value-of select="." />
                </i18n:text>
                <xsl:if test="../@required = 'true'">
                    <xsl:text> </xsl:text>
                    <span class="required">*</span>
                </xsl:if>
                <xsl:text>:</xsl:text>
            </xsl:if>
        </label>
    </xsl:template>

    <xsl:template match="@description" name="description">
        <xsl:if test=". != '' or ../message">
            <span class="message">
                <xsl:for-each select="../message">
                    <i18n:text>
                        <xsl:value-of select="." />
                    </i18n:text>
                    <xsl:text> </xsl:text>
                </xsl:for-each>
                <xsl:text> </xsl:text>
                <xsl:if test=". != ''">
                    <i18n:text>
                        <xsl:value-of select="." />
                    </i18n:text>
                </xsl:if>
            </span>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
