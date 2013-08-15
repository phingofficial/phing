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
  <xsl:import href="html-common.xsl"/>

</xsl:stylesheet>
