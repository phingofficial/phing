<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    * ==============================================================================
    * Common XSL Docbook5 customization for all stylesheets
    *    
    * Revision: $Id$
    * ==============================================================================    
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:param name="formal.title.placement"> figure after example after equation after table before
        procedure before </xsl:param>

    <xsl:param name="local.l10n.xml" select="document('')"/>
    <l:i18n xmlns:l="http://docbook.sourceforge.net/xmlns/l10n/1.0">
        <l:l10n language="en">

            <l:context name="title-unnumbered">
                <l:template name="appendix" text="%t"/>
                <l:template name="article/appendix" text="%t"/>
                <l:template name="bridgehead" text="%t"/>
                <l:template name="chapter" text="%t"/>
                <l:template name="sect1" text="%t"/>
                <l:template name="sect2" text="%t"/>
                <l:template name="sect3" text="%t"/>
                <l:template name="sect4" text="%t"/>
                <l:template name="sect5" text="%t"/>
                <l:template name="section" text="%t"/>
                <l:template name="simplesect" text="%t"/>
                <l:template name="part" text="%t"/>
            </l:context>

            <l:context name="title-numbered">
                <l:template name="appendix" text="Appendix %n. %t"/>
                <l:template name="article/appendix" text="Appendix %n - %t"/>
                <l:template name="bridgehead" text="%n %t"/>
                <l:template name="chapter" text="Chapter %n %t"/>
                <l:template name="part" text="Part %n %t"/>
                <l:template name="sect1" text="%n %t"/>
                <l:template name="sect2" text="%n %t"/>
                <l:template name="sect3" text="%n %t"/>
                <l:template name="sect4" text="%n %t"/>
                <l:template name="sect5" text="%n %t"/>
                <l:template name="section" text="%n %t"/>
                <l:template name="simplesect" text="%t"/>
            </l:context>

        </l:l10n>
    </l:i18n>
</xsl:stylesheet>
