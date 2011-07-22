<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns="http://www.w3.org/1999/xhtml" 
    xmlns:php="http://php.net/xsl" 
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    exclude-result-prefixes="php xhtml i18n"
    >
    
    <!-- form -->
    <xsl:template match="form" mode="form">
        <xsl:apply-templates select="message" mode="form"/>
        <xsl:apply-templates select="errors" mode="form"/>
    </xsl:template>
    
    <!-- errors -->
    <xsl:template match="errors" mode="form">
        <xsl:if test="error">
            <div class="error">
                <xsl:if test="error[@type='required']">
                    <strong><i18n:text>Bitte füllen Sie die folgenden Felder aus:</i18n:text></strong>
                    <ul>
                        <xsl:apply-templates select="error[@type='required']" mode="form"/>
                    </ul>
                </xsl:if>
                
                <xsl:if test="error[@type!='required']">
                    <strong><i18n:text>Bitte korrigieren Sie folgende Fehler:</i18n:text></strong>
                    <ul>
                        <xsl:apply-templates select="error[@type!='required']" mode="form"/>
                    </ul>
                </xsl:if>
                
            </div>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="error[@type='required']" mode="form">
        <li><i18n:text><xsl:value-of select="@description"/></i18n:text></li>
    </xsl:template>

    <xsl:template match="error" mode="form">
        <!-- only show text errors for fields if text exists -->
        <xsl:if test="string-length(text()) > 0">
            <li><i18n:text><xsl:value-of select="@description"/></i18n:text><xsl:text>: </xsl:text><i18n:text><xsl:value-of select="."/></i18n:text></li>
        </xsl:if>
    </xsl:template>

    <!-- messages -->
    <xsl:template match="message" mode="form">
        <div class="message">
            <strong><i18n:text><xsl:apply-templates select="./text()"/></i18n:text></strong>
        </div>
    </xsl:template>
	
    <!-- form sections -->
    <xsl:template match="section" mode="form">
        <xsl:if test="@description">
            <h3>
                <i18n:text><xsl:value-of select="@description"/></i18n:text>
            </h3>
        </xsl:if>
        <xsl:apply-templates select="field" mode="form"/>
    </xsl:template>
    
    <!-- fields -->
    <xsl:template match="field" mode="form">
        <p>
            <xsl:apply-templates select="." mode="label"/>
            <xsl:apply-templates select="." mode="field"/>
        </p>
    </xsl:template>
    
    <xsl:template match="field[@type='file']" mode="field">
        <input type="file">
            <xsl:apply-templates select="@*" mode="field"/>
        </input>
    </xsl:template>
    
    <xsl:template match="field[@type='checkbox']" mode="field">
        <span>
            <input type="checkbox" value="{@name}" class="checkbox">
            <xsl:apply-templates select="@*" mode="field"/>
            <xsl:if test="@value"><xsl:attribute name="checked"><xsl:text>checked</xsl:text></xsl:attribute></xsl:if>
            </input>
        <xsl:text> </xsl:text><i18n:text><xsl:value-of select="@text"/></i18n:text>
        </span>
    </xsl:template>
    
    <xsl:template match="field[@type='select']" mode="field">
        <select class="select">
            <xsl:apply-templates select="@*" mode="field"/>
            <xsl:apply-templates select="values/*" mode="field"/>
        </select>
    </xsl:template>

    <xsl:template match="optgroup" mode="field">
        <optgroup label="{@label}">
            <xsl:apply-templates select="option" mode="field"/>
        </optgroup>
    </xsl:template>
    
    <xsl:template match="option" mode="field">
        <option value="{@value}">
            <xsl:if test="@value = ../../@value">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <i18n:text><xsl:value-of select="."/></i18n:text>
        </option>
    </xsl:template>
    
    <xsl:template match="field[@type='textarea']" mode="field">
        <textarea cols="60" rows="10" class="textarea">
            <xsl:apply-templates select="@*" mode="field"/>
            <xsl:value-of select="@value"/>
        </textarea>
    </xsl:template>
    
    <xsl:template match="field[@type='text_tiny']" mode="field">
        <input type="text" class="text mini" value="{@value}">
            <xsl:apply-templates select="@*" mode="field"/>
        </input>
    </xsl:template>
    
    <xsl:template match="field[@type='text']" mode="field">
        <input type="text" class="text" value="{@value}">
            <xsl:apply-templates select="@*" mode="field"/>
        </input>
    </xsl:template>

    <xsl:template match="field[@type='password']" mode="field">
        <input type="password" class="password" value="{@value}">
            <xsl:apply-templates select="@*" mode="field"/>
        </input>
    </xsl:template>

    <xsl:template match="field[@type='hidden']" mode="field">
        <input type="hidden" value="{@value}">
            <xsl:apply-templates select="@*" mode="field"/>
        </input>
    </xsl:template>
    
    <!-- field attributes -->
    <xsl:template match="@name" mode="field">
        <xsl:attribute name="id"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
    </xsl:template>
    
    <!-- remove unwanted attributes -->
    
    <xsl:template match="@longdescription" mode="field">
        <xsl:attribute name="style"><xsl:text>margin-bottom: 20px;</xsl:text></xsl:attribute>
    </xsl:template>
    
    <xsl:template match="@required|@description|@type|@text|@value|@allow_empty" mode="field"/>
    
    <xsl:template match="@*" mode="field">
        <xsl:copy-of select="."/>
    </xsl:template>
    
    <!-- labels -->
    <xsl:template match="field[@type != 'hidden']" mode="label">
        <xsl:variable name="fieldName" select="@name"/>
        <label for="{@name}">
            <xsl:attribute name="class">
                <xsl:if test="@type = 'textarea'">block</xsl:if>
                <xsl:if test="../../../errors/error[@fieldname=$fieldName]"> formerror</xsl:if>
            </xsl:attribute>
            <i18n:text>
                <xsl:choose>
                    <xsl:when test="@description"><xsl:value-of select="@description"/></xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                </xsl:choose>
            </i18n:text>
            <xsl:if test="@required='required'">*</xsl:if>
        </label>
    </xsl:template>
    

</xsl:stylesheet>
