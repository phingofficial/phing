<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:import href="${phing.documentation.docbook.stylesheets.xhtml}"/>

    <!--
        Parameters.
    -->
    <xsl:param
        name="base.dir"
        select="'${phing.documentation.xhtml.buildDirectory}'" />
    <xsl:param
        name="chunker.output.encoding"
        select="'UTF-8'" />
    <xsl:param
        name="chunker.output.indent"
        select="'yes'" />
    <xsl:param
        name="section.autolabel"
        select="1" />
    <xsl:param
        name="section.label.includes.component.label"
        select="1" />
    <xsl:param
        name="use.id.as.filename"
        select="1" />
</xsl:stylesheet>
