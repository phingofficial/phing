<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8" indent="no"/>
<xsl:template match="/project">@startuml
<xsl:variable name="name" select="@name"/>
title <xsl:value-of select="$name"/>
skinparam ArrowFontColor Black
skinparam ArrowThickness 2
skinparam UseCaseBackgroundColor #FFFECC
skinparam UseCaseBorderColor #333333
skinparam UseCaseBorderThickness 2
skinparam UseCaseFontColor Black
</xsl:template>
</xsl:stylesheet>
