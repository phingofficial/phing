<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns:d="http://docbook.org/ns/docbook"
  xmlns:xslthl="http://xslthl.sf.net" exclude-result-prefixes="xslthl d" version="1.0">
  
  <!-- ********************************************************************
     $Id$
     ********************************************************************

     This file is part of the XSL DocBook Stylesheet distribution.
     See ../README or http://docbook.sf.net/release/xsl/current/ for
     and other information.

     Customized for Phing
          
     ******************************************************************** 
  -->
  <xsl:import href="/usr/share/xml/docbook/stylesheet/nwalsh5/current/highlighting/common.xsl"/>
  
  <xsl:template match="xslthl:keyword" mode="xslthl">
    <span class="hl-keyword">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:string" mode="xslthl">
    <span class="hl-string">
        <xsl:apply-templates mode="xslthl"/>     
    </span>
  </xsl:template>
  <xsl:template match="xslthl:comment" mode="xslthl">
    <span class="hl-comment">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:directive" mode="xslthl">
    <span class="hl-directive">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:tag" mode="xslthl">
    <span class="hl-tag">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:attribute" mode="xslthl">
    <span class="hl-attribute">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:value" mode="xslthl">
    <span class="hl-value">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:html" mode="xslthl">
      <span class="hl-html">
        <xsl:apply-templates mode="xslthl"/>
      </span>   
  </xsl:template>
  <xsl:template match="xslthl:xslt" mode="xslthl">
    <span class="hl-xslt">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>  
  <xsl:template match="xslthl:section" mode="xslthl">
    <span class="hl-section">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:number" mode="xslthl">
    <span class="hl-number">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
  <xsl:template match="xslthl:annotation" mode="xslthl">
      <span class="hl-annotation">
        <xsl:apply-templates mode="xslthl"/>
      </span>   
  </xsl:template>
  <xsl:template match="xslthl:doccomment" mode="xslthl">
    <span class="hl-doccomment"><xsl:apply-templates mode="xslthl"/></span>
  </xsl:template>
  <xsl:template match="xslthl:doctype" mode="xslthl">
    <span class="hl-doctype">
      <xsl:apply-templates mode="xslthl"/>
    </span>
  </xsl:template>
</xsl:stylesheet>
