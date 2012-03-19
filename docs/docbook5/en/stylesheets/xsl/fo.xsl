<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    * ==============================================================================
    * Customization layer for fo XSL Docbook5 used to produce PDF output
    *    
    * Revision: $Id$
    * ==============================================================================    
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:d="http://docbook.org/ns/docbook"
    version="1.0">
    <xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh5/current/fo/docbook.xsl"/>
    <xsl:import href="common.xsl"/>
    <xsl:import href="fo-common.xsl"/>

    <xsl:template match="d:programlisting">
        <fo:block xsl:use-attribute-sets="monospace.verbatim.properties programlisting.style">
            <xsl:attribute name="writing-mode">lr-tb</xsl:attribute>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    
    </xsl:stylesheet>
