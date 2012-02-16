<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    * ==============================================================================
    * Customization layer for fo XSL Docbook5 used to produce PDF output with
    * source highlight
    *    
    * Revision: $Id$
    * ==============================================================================    
-->    
<xsl:stylesheet xmlns:d="http://docbook.org/ns/docbook"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" 
    version="1.0">
    <xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh5/current/fo/docbook.xsl"/>
    <xsl:import href="common.xsl"/>
    <xsl:import href="fo-common.xsl"/>
    <xsl:import href="fo-highlight.xsl"/>

    <xsl:param name="highlight.source" select="1"/>

    <xsl:attribute-set name="shade.verbatim.style">
        <xsl:attribute name="border-top">solid</xsl:attribute>
        <xsl:attribute name="border-left">solid</xsl:attribute>
        <xsl:attribute name="border-top-width">0.5pt</xsl:attribute>
        <xsl:attribute name="border-left-width">0.5pt</xsl:attribute>
        <xsl:attribute name="border-right">solid</xsl:attribute>
        <xsl:attribute name="border-right-width">2pt</xsl:attribute>
        <xsl:attribute name="border-bottom">solid</xsl:attribute>
        <xsl:attribute name="border-bottom-width">2pt</xsl:attribute>
        <xsl:attribute name="border-color">#777777</xsl:attribute>
        <xsl:attribute name="background-color">#eee</xsl:attribute>
        <xsl:attribute name="padding">3pt</xsl:attribute>
        <xsl:attribute name="font-size">8.5pt</xsl:attribute>
        <xsl:attribute name="margin-left">6pt</xsl:attribute>
        <xsl:attribute name="margin-right">1pt</xsl:attribute>
    </xsl:attribute-set>

</xsl:stylesheet>
