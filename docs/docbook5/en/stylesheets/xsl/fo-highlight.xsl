<?xml version='1.0'?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:d="http://docbook.org/ns/docbook"
		xmlns:fo="http://www.w3.org/1999/XSL/Format"
		xmlns:xslthl="http://xslthl.sf.net"
                exclude-result-prefixes="xslthl d"
                version='1.0'>

  <!-- ********************************************************************
       $Id$
       ********************************************************************

This file is part of the XSL DocBook Stylesheet distribution.
See ../README or http://docbook.sf.net/release/xsl/current/ for
and other information.

******************************************************************** -->

  <xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh5/current/highlighting/common.xsl"/>

  <xsl:template match='xslthl:keyword' mode="xslthl">
    <fo:inline font-weight="bold"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:string' mode="xslthl">
    <fo:inline font-weight="normal" font-style="italic" color="darkred"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:comment' mode="xslthl">
    <fo:inline font-style="italic" color="green"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:tag' mode="xslthl">
    <fo:inline font-weight="bold" color="blue"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:attribute' mode="xslthl">
    <fo:inline font-weight="bold"><xsl:apply-templates mode="xslthl"/><xsl:text> </xsl:text></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:value' mode="xslthl">
    <fo:inline font-weight="normal" color="darkred"><xsl:text> </xsl:text><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <!--
      <xsl:template match='xslthl:html'>
      <span style='background:#AFF'><font color='blue'><xsl:apply-templates/></font></span>
      </xsl:template>

<xsl:template match='xslthl:xslt'>
<span style='background:#AAA'><font color='blue'><xsl:apply-templates/></font></span>
</xsl:template>

<xsl:template match='xslthl:section'>
<span style='background:yellow'><xsl:apply-templates/></span>
</xsl:template>
  -->

  <xsl:template match='xslthl:number' mode="xslthl">
    <fo:inline color="blue"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:annotation' mode="xslthl">
    <fo:inline color="gray"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:directive' mode="xslthl">
    <fo:inline color="red"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:doccomment' mode="xslthl">
    <fo:inline font-weight="bold" font-style="italic" color="green"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>

  <xsl:template match='xslthl:doctype' mode="xslthl">
    <fo:inline font-weight="bold" font-style="italic" color="green"><xsl:apply-templates mode="xslthl"/></fo:inline>
  </xsl:template>
  
</xsl:stylesheet>

