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
  <xsl:import href="html.xsl"/> 
  <xsl:import href="html-highlight.xsl"/>
  <xsl:param name="highlight.source" select="1"/>
</xsl:stylesheet>
