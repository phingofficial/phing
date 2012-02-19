<?xml version="1.0" encoding="UTF-8"?>
<!-- 
  * ==============================================================================
  * Customization layer for fo XSL Docbook5 used to produce HTML output
  * Note that we actually use the "onechunk" stylesheet instead of "html"
  * stylesheet since we need to set encoding to utf-8 which is not possible in
  * HTML stylesheet.
  *    
  * Revision: $Id$
  * ==============================================================================    
-->
<xsl:stylesheet xmlns:d="http://docbook.org/ns/docbook"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh5/current/html/onechunk.xsl"/>
  <xsl:import href="common.xsl"/>
  <xsl:import href="html.xsl"/>

  <!--  
   * ==============================================================================
   * Customize the screen verbatim environment to add the fancy "XTerm" look
   * Unfortunately we must override the entire template.
   * The fancy look does not support line numbering.
   * ==============================================================================
 -->

  <xsl:template match="d:programlisting|d:screen|d:synopsis">
    <xsl:param name="suppress-numbers" select="'0'"/>
    <xsl:variable name="id">
      <xsl:call-template name="object.id"/>
    </xsl:variable>

    <xsl:call-template name="anchor"/>

    <xsl:variable name="div.element">
      <xsl:choose>
        <xsl:when test="$make.clean.html != 0">div</xsl:when>
        <xsl:otherwise>pre</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>

    <xsl:if test="$shade.verbatim != 0">
      <xsl:message>
        <xsl:text>The shade.verbatim parameter is deprecated. </xsl:text>
        <xsl:text>Use CSS instead,</xsl:text>
      </xsl:message>
      <xsl:message>
        <xsl:text>for example: pre.</xsl:text>
        <xsl:value-of select="local-name(.)"/>
        <xsl:text> { background-color: #E0E0E0; }</xsl:text>
      </xsl:message>
    </xsl:if>

    <xsl:choose>
      <xsl:when
        test="$suppress-numbers = '0'
        and @linenumbering = 'numbered'
        and $use.extensions != '0'
        and $linenumbering.extension != '0'">
        <xsl:variable name="rtf">
          <xsl:choose>
            <xsl:when test="$highlight.source != 0">
              <xsl:call-template name="apply-highlighting"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:apply-templates/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:element name="{$div.element}">
          <xsl:apply-templates select="." mode="common.html.attributes"/>
          <xsl:if test="@width != ''">
            <xsl:attribute name="width">
              <xsl:value-of select="@width"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:call-template name="number.rtf.lines">
            <xsl:with-param name="rtf" select="$rtf"/>
          </xsl:call-template>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <!-- 
          This is the customization for the fancy XTerm. This is mainly driven by the
          CSS stylesheet. We render the pre tag inside a "terminal" div which is later
          styled by the CSS sheet. We only apply this to the "screen" tag and leave the
          others.
        -->
        <xsl:choose>
          <xsl:when test="local-name(.) = 'screen'">
            <div class="terminal">
              <div class="terminaltop"> </div>
              <xsl:element name="{$div.element}">
                <xsl:apply-templates select="." mode="common.html.attributes"/>
                <xsl:if test="@width != ''">
                  <xsl:attribute name="width">
                    <xsl:value-of select="@width"/>
                  </xsl:attribute>
                </xsl:if>
                <xsl:choose>
                  <xsl:when test="$highlight.source != 0">
                    <xsl:call-template name="apply-highlighting"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:apply-templates/>
                  </xsl:otherwise>
                </xsl:choose>
              </xsl:element>
              <div class="terminalbottom"> </div>
            </div>
          </xsl:when>
          <xsl:otherwise>
            <xsl:element name="{$div.element}">
              <xsl:apply-templates select="." mode="common.html.attributes"/>
              <xsl:if test="@width != ''">
                <xsl:attribute name="width">
                  <xsl:value-of select="@width"/>
                </xsl:attribute>
              </xsl:if>
              <xsl:choose>
                <xsl:when test="$highlight.source != 0">
                  <xsl:call-template name="apply-highlighting"/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:apply-templates/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:element>
          </xsl:otherwise>
        </xsl:choose>
        <!-- End of customiziation of this template -->
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
