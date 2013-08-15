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
  <xsl:import href="common.xsl"/>

  <xsl:param name="chunker.output.encoding" select="'UTF-8'"/>
  <xsl:param name="section.autolabel" select="1"/>
  <xsl:param name="section.autolabel.max.depth" select="2"/>
  <xsl:param name="section.label.includes.component.label" select="1"/>
  <xsl:param name="generate.section.toc.level" select="0"/>
  <xsl:param name="toc.max.depth" select="2"/>
  <xsl:param name="emphasis.propagates.style" select="1"/>
  <xsl:param name="para.propagates.style" select="1"/>
  <xsl:param name="xref.with.number.and.title" select="1"/>
  <xsl:param name="make.valid.html" select="0"/>
  <xsl:param name="html.stylesheet" select="'book.css'"/>
  <!-- Only include top level Book components in the TOC
       This means no example, figures, programlisting etc.
  -->
  <xsl:param name="generate.toc" select="'book toc'"/>
  
  <!--  
   * ==============================================================================
   * Customize the titles for figures and tables. This will break the heading from
   * the description so the HTML output  will for example look like
   * <p class="formal.object.title">
   *    <span class="formal.object.title">Figure 1:&nbsp;</span>
   *    <span class="formal.object.description"> some description text ... </span>
   * </p>
   * ==============================================================================
 -->
  <xsl:template name="formal.object.heading">
    <xsl:param name="object" select="."/>
    <xsl:param name="title">
      <xsl:apply-templates select="$object" mode="object.title.markup">
        <xsl:with-param name="allow-anchors" select="1"/>
      </xsl:apply-templates>
    </xsl:param>

    <xsl:choose>
      <xsl:when test="$make.clean.html != 0">
        <xsl:variable name="html.class" select="concat(local-name($object),'-title')"/>
        <div class="{$html.class}">
          <span class="label">
            <xsl:call-template name="gentext">
              <xsl:with-param name="key" select="local-name($object)"/>
            </xsl:call-template>
            <xsl:text> </xsl:text>
            <xsl:apply-templates select="$object" mode="label.markup"/>
            <xsl:text>:&#160;</xsl:text>
          </span>
          <span class="title">
            <xsl:apply-templates select="$object" mode="title.markup"/>
          </span>
        </div>
      </xsl:when>
      <xsl:otherwise>
        <p class="formal-object-title">
          <span class="label">
            <xsl:call-template name="gentext">
              <xsl:with-param name="key" select="local-name($object)"/>
            </xsl:call-template>
            <xsl:text> </xsl:text>
            <xsl:apply-templates select="$object" mode="label.markup"/>
            <xsl:text>:&#160;</xsl:text>
          </span>
          <span class="title">
            <xsl:apply-templates select="$object" mode="title.markup"/>
          </span>
        </p>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- 
  =============================================================================
  We are forced to copy the toc.line template from the docbook stylesheet in 
  order to customize it so we can add the word "Appendix" before the 
  appendix titles in the TOC.
  
  Note that in order to have the word "Appendix" added to the actual section
  headers we have to modify the text templates. This is done in "common.xsl"
  =============================================================================
  -->


  <xsl:template name="toc.line">
    <xsl:param name="toc-context" select="."/>
    <xsl:param name="depth" select="1"/>
    <xsl:param name="depth.from.context" select="8"/>
    
    <span>
      <xsl:attribute name="class"><xsl:value-of select="local-name(.)"/></xsl:attribute>
      
      <!-- * if $autotoc.label.in.hyperlink is zero, then output the label -->
      <!-- * before the hyperlinked title (as the DSSSL stylesheet does) -->
      <xsl:if test="$autotoc.label.in.hyperlink = 0">
        <xsl:variable name="label">
          <xsl:apply-templates select="." mode="label.markup"/>
        </xsl:variable>
        <xsl:copy-of select="$label"/>
        <xsl:if test="$label != ''">
          <xsl:value-of select="$autotoc.label.separator"/>
        </xsl:if>
      </xsl:if>
      
      <a>
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="context" select="$toc-context"/>
            <xsl:with-param name="toc-context" select="$toc-context"/>
          </xsl:call-template>
        </xsl:attribute>
        
        <!-- * if $autotoc.label.in.hyperlink is non-zero, then output the label -->
        <!-- * as part of the hyperlinked title -->
        <xsl:if test="not($autotoc.label.in.hyperlink = 0)">
          <xsl:variable name="label">
            <xsl:choose>
              <xsl:when test="self::d:appendix">
                <xsl:text>Appendix </xsl:text>
                <xsl:apply-templates select="." mode="label.markup"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:apply-templates select="." mode="label.markup"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:variable>
          <xsl:copy-of select="$label"/>
          <xsl:if test="$label != ''">
            <xsl:value-of select="$autotoc.label.separator"/>
          </xsl:if>
        </xsl:if>
        
        <xsl:apply-templates select="." mode="titleabbrev.markup"/>
      </a>
    </span>
  </xsl:template>
  
<!-- 
   * ==============================================================================
   * Customization of the bibliography page
   * We add a <span> around the label to be able to have it in bold style
   * ==============================================================================
-->
<xsl:template name="biblioentry.label">
  <xsl:param name="node" select="."/>

  <xsl:choose>
    <xsl:when test="$bibliography.numbered != 0">
      <span class="biblio-label">
      <xsl:text>[</xsl:text>
      <xsl:number from="d:bibliography" count="d:biblioentry|d:bibliomixed"
                  level="any" format="1"/>
      <xsl:text>] </xsl:text>
      </span>
    </xsl:when>
    <xsl:when test="local-name($node/child::*[1]) = 'abbrev'">
      <span class="biblio-label">
      <xsl:text>[</xsl:text>
      <xsl:apply-templates select="$node/d:abbrev[1]"/>
      <xsl:text>] </xsl:text>
      </span>
    </xsl:when>
    <xsl:when test="$node/@xreflabel">
      <span class="biblio-label">
      <xsl:text>[</xsl:text>
      <xsl:value-of select="$node/@xreflabel"/>
      <xsl:text>] </xsl:text>
      </span>
    </xsl:when>
    <xsl:when test="$node/@id">
      <span class="biblio-label">
      <xsl:text>[</xsl:text>
      <xsl:value-of select="$node/@id"/>
      <xsl:text>] </xsl:text>
      </span>
    </xsl:when>
    <xsl:when test="$node/@xml:id">
      <span class="biblio-label">
      <xsl:text>[</xsl:text>
      <xsl:value-of select="$node/@xml:id"/>
      <xsl:text>] </xsl:text>
      </span>
    </xsl:when>
    <xsl:otherwise><!-- nop --></xsl:otherwise>
  </xsl:choose>
</xsl:template>


</xsl:stylesheet>
