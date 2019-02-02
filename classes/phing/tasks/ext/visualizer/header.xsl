<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8" indent="no"/>
<xsl:template match="/project">@startuml
<xsl:variable name="name" select="@name"/>
title <xsl:value-of select="$name"/>
skinparam ArrowColor #555555
skinparam ArrowFontColor #555555
skinparam UseCaseBackgroundColor #FFFFCC
skinparam UseCaseBorderColor #555555
</xsl:template>
</xsl:stylesheet>
