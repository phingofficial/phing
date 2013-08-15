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

    <xsl:param name="paper.type" select="'A4'"/>
    <xsl:param name="shade.verbatim" select="1"/>
    <xsl:param name="double.sided" select="1"/>
    <xsl:param name="fop1.extensions" select="1"/>
    <xsl:param name="section.autolabel" select="1"/>
    <xsl:param name="section.autolabel.max.depth" select="2"/>
    <xsl:param name="section.label.includes.component.label" select="1"/>
    <xsl:param name="toc.max.depth" select="3"/>
    <xsl:param name="page.margin.outer">1.5cm</xsl:param>
    <xsl:param name="page.margin.inner">2cm</xsl:param>
    <xsl:param name="table.cell.border.style" select="'none'"/>
    <xsl:param name="table.frame.border.color" select="'#666666'"/>
    <xsl:param name="table.frame.border.thickness" select="'2pt'"/>
    <xsl:param name="table.frame.border.style" select="'double'"/>
    <xsl:param name="default.table.frame" select="'topbot'"/>
    <xsl:param name="generate.toc" select="'book toc'"/>
    <xsl:param name="body.font.family">sans-serif</xsl:param>


    <xsl:template match="processing-instruction('hard-pagebreak')">
        <fo:block break-after="page"/>
    </xsl:template>

    <!-- 
        ==========================================================================
        Title page title formatting
        ==========================================================================
    -->
    <xsl:attribute-set name="component.title.properties">
        <xsl:attribute name="color">#317c9c</xsl:attribute>
    </xsl:attribute-set>

    <xsl:template name="book.titlepage.recto">
        <fo:block text-align="center">
            <fo:external-graphic src="url(/tmp/_tmpxslimg/images/phing.svg)" content-width="15cm"/>
        </fo:block>

        <xsl:choose>
            <xsl:when test="bookinfo/title">
                <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="bookinfo/title"/>
            </xsl:when>
            <xsl:when test="info/title">
                <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="info/title"/>
            </xsl:when>
            <xsl:when test="title">
                <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="title"/>
            </xsl:when>
        </xsl:choose>

        <xsl:choose>
            <xsl:when test="bookinfo/subtitle">
                <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="bookinfo/subtitle"/>
            </xsl:when>
            <xsl:when test="info/subtitle">
                <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="info/subtitle"/>
            </xsl:when>
            <xsl:when test="subtitle">
                <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="subtitle"/>
            </xsl:when>
        </xsl:choose>

        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="bookinfo/corpauthor"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="info/corpauthor"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="bookinfo/authorgroup"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="info/authorgroup"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="bookinfo/author"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="info/author"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="bookinfo/itermset"/>
        <xsl:apply-templates mode="book.titlepage.recto.auto.mode" select="info/itermset"/>
    </xsl:template>

    <!-- 
        ==========================================================================
        Section title formatting
        ==========================================================================
    -->
    <xsl:attribute-set name="section.title.level1.properties">
        <xsl:attribute name="font-size">20pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-family">sans-serif</xsl:attribute>
        <xsl:attribute name="color">#317c9c</xsl:attribute>
        <xsl:attribute name="space-before.minimum">1.5em</xsl:attribute>
        <xsl:attribute name="space-before.optimum">2.0em</xsl:attribute>
        <xsl:attribute name="space-before.maximum">3.0em</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="section.title.level2.properties">
        <xsl:attribute name="font-size">12pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-family">sans-serif</xsl:attribute>
        <xsl:attribute name="color">#317c9c</xsl:attribute>
        <xsl:attribute name="space-before.minimum">1.5em</xsl:attribute>
        <xsl:attribute name="space-before.optimum">2.0em</xsl:attribute>
        <xsl:attribute name="space-before.maximum">3.0em</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="section.title.level3.properties">
        <xsl:attribute name="font-size">11pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-family">sans-serif</xsl:attribute>
        <xsl:attribute name="color">#317c9c</xsl:attribute>
        <xsl:attribute name="margin-left">3em</xsl:attribute>
        <xsl:attribute name="space-before.minimum">1.5em</xsl:attribute>
        <xsl:attribute name="space-before.optimum">2.0em</xsl:attribute>
        <xsl:attribute name="space-before.maximum">3.0em</xsl:attribute>
    </xsl:attribute-set>


    <!-- 
        ==========================================================================
        Screen formatting
        ==========================================================================
    -->
    <xsl:attribute-set name="shade.verbatim.style">
        <xsl:attribute name="border-style">solid</xsl:attribute>
        <xsl:attribute name="border-width">0.5pt</xsl:attribute>
        <xsl:attribute name="border-color">#666666</xsl:attribute>
        <xsl:attribute name="background-color">#EEEEEE</xsl:attribute>
        <xsl:attribute name="color">#000000</xsl:attribute>
        <xsl:attribute name="padding">3pt</xsl:attribute>
        <xsl:attribute name="font-size">8.2pt</xsl:attribute>
        <xsl:attribute name="font-weight">normal</xsl:attribute>
        <xsl:attribute name="margin-left">6pt</xsl:attribute>
        <xsl:attribute name="margin-right">1pt</xsl:attribute>
    </xsl:attribute-set>


    <xsl:attribute-set name="programlisting.style">
        <xsl:attribute name="border-style">solid</xsl:attribute>
        <xsl:attribute name="border-width">0.5pt</xsl:attribute>
        <xsl:attribute name="border-color">#666666</xsl:attribute>
        <xsl:attribute name="background-color">#EEEEEE</xsl:attribute>
        <xsl:attribute name="color">#000000</xsl:attribute>
        <xsl:attribute name="padding">3pt</xsl:attribute>
        <xsl:attribute name="font-size">8.2pt</xsl:attribute>
        <xsl:attribute name="font-weight">normal</xsl:attribute>
        <xsl:attribute name="margin-left">6pt</xsl:attribute>
        <xsl:attribute name="margin-right">1pt</xsl:attribute>
    </xsl:attribute-set>

    <!--
        ==================================================================================
        Formatting for <acronym>, <command> and <application> elements.
        By default they are not styled. 
        ==================================================================================
    -->
    <!-- Utility template to format an inline bold-italic character sequence -->
    <xsl:template name="inline.bolditalicseq">
        <xsl:param name="content">
            <xsl:call-template name="simple.xlink">
                <xsl:with-param name="content">
                    <xsl:apply-templates/>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:param>

        <fo:inline font-style="italic" font-weight="bold">
            <xsl:call-template name="anchor"/>
            <xsl:if test="@dir">
                <xsl:attribute name="direction">
                    <xsl:choose>
                        <xsl:when test="@dir = 'ltr' or @dir = 'lro'">ltr</xsl:when>
                        <xsl:otherwise>rtl</xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </xsl:if>
            <xsl:copy-of select="$content"/>
        </fo:inline>
    </xsl:template>

    <xsl:template match="d:acronym">
        <xsl:call-template name="inline.bolditalicseq"/>
    </xsl:template>

    <xsl:template match="d:command">
        <xsl:call-template name="inline.boldmonoseq"/>
    </xsl:template>

    <xsl:template match="d:application">
        <xsl:call-template name="inline.italicseq"/>
    </xsl:template>

    <!--        
        ==================================================================================
        Some generic markup to display some colored text
        Note: We use <phrase> tag and not <emphasis> since <phrase> is really menant to be
        used for this kind of semi-visual markup.
        ==================================================================================
    -->
    <xsl:template match="d:phrase[@role='red']">
        <fo:inline color="red">
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="d:phrase[@role='blue']">
        <fo:inline color="blue">
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>

    <!--
    <xsl:template match="d:programlisting">
        <fo:block xsl:use-attribute-sets="monospace.verbatim.properties programlisting.style">
            <xsl:attribute name="writing-mode">lr-tb</xsl:attribute>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
-->

    <!--
        ==================================================================================
        ADMONITION formatting
         - Custom images with correct size to avoid scaling
         - Title formatting
         - Cust. template to get thick borders on top and bottom and a thin line
           below the title.
         - Make the default font 90% of standard font size
        ==================================================================================
    -->

    <!-- Should graphics be used for admonitions (notes, warnings)? 0 or 1 -->
    <xsl:param name="admon.graphics" select="1"/>

    <!-- Directory where to find graphics. Full path from the root with trailing '/' -->
    <xsl:param name="admon.graphics.path"
        >/tmp/_tmpxslimg/images/</xsl:param>

    <!-- File extension for grphic files -->
    <xsl:param name="admon.graphics.extension" select="'.svg'"/>

    <!-- Maker sure our customized admonitions are not scaled since that looks ugly.
    This means that the size needs to match the original size of the icons -->
    <xsl:template match="note" mode="admon.graphic.width">
        <xsl:text>48px</xsl:text>
    </xsl:template>

    <xsl:template match="caution" mode="admon.graphic.width">
        <xsl:text>48px</xsl:text>
    </xsl:template>

    <xsl:template match="tip" mode="admon.graphic.width">
        <xsl:text>28px</xsl:text>
    </xsl:template>

    <xsl:template match="warning" mode="admon.graphic.width">
        <xsl:text>28px</xsl:text>
    </xsl:template>

    <xsl:template match="*" mode="admon.graphic.width">
        <xsl:text>32pt</xsl:text>
    </xsl:template>

    <!-- Setup admonition title properties -->
    <xsl:attribute-set name="admonition.title.properties">
        <xsl:attribute name="font-size">90%</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-family">sans-serif</xsl:attribute>
        <xsl:attribute name="color">#317c9c</xsl:attribute>
        <!-- Dark orange -->
    </xsl:attribute-set>

    <!-- Setup the admonition body properties -->
    <xsl:attribute-set name="admonition.properties">
        <xsl:attribute name="font-size">90%</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="graphical.admonition.properties">
        <xsl:attribute name="space-before.minimum">2em</xsl:attribute>
        <xsl:attribute name="space-before.optimum">2.5em</xsl:attribute>
        <xsl:attribute name="space-before.maximum">3em</xsl:attribute>
        <xsl:attribute name="space-after.minimum">2em</xsl:attribute>
        <xsl:attribute name="space-after.optimum">2.5em</xsl:attribute>
        <xsl:attribute name="space-after.maximum">3em</xsl:attribute>
    </xsl:attribute-set>

    <!-- We need to customize the template wince we want to have lines
    above and below the admonition. Note that the way we do it we only 
    add lines over the actual body part. The lines do not stretch to the
    left margin over and above the graphic. If you want to do that you need
    to add the borders in the sourrounding fo:block on the 8:th line in 
    this template -->
    <xsl:template name="graphical.admonition">
        <xsl:variable name="id">
            <xsl:call-template name="object.id"/>
        </xsl:variable>
        <xsl:variable name="graphic.width">
            <xsl:apply-templates select="." mode="admon.graphic.width"/>
        </xsl:variable>
        <fo:block id="{$id}" xsl:use-attribute-sets="graphical.admonition.properties">
            <fo:list-block provisional-distance-between-starts="{$graphic.width} + 18pt"
                provisional-label-separation="18pt">
                <fo:list-item>
                    <fo:list-item-label end-indent="label-end()">
                        <fo:block>
                            <fo:external-graphic width="auto" height="auto"
                                content-width="{$graphic.width}">
                                <xsl:attribute name="src">
                                    <xsl:call-template name="admon.graphic"/>
                                </xsl:attribute>
                            </fo:external-graphic>
                        </fo:block>
                    </fo:list-item-label>
                    <fo:list-item-body start-indent="body-start()">
                        <xsl:if test="$admon.textlabel != 0 or d:title or d:info/d:title">
                            <fo:block border-top="2pt solid #317c9c"
                                border-bottom="0.5pt solid #317c9c" padding-top="4pt"
                                xsl:use-attribute-sets="admonition.title.properties">
                                <xsl:apply-templates select="." mode="object.title.markup">
                                    <xsl:with-param name="allow-anchors" select="1"/>
                                </xsl:apply-templates>
                            </fo:block>
                        </xsl:if>
                        <fo:block border-bottom="2pt solid #317c9c" padding-top="1pt"
                            padding-bottom="4pt" xsl:use-attribute-sets="admonition.properties">
                            <xsl:apply-templates/>
                        </fo:block>
                    </fo:list-item-body>
                </fo:list-item>
            </fo:list-block>
        </fo:block>
    </xsl:template>

    <!-- Include figures and tables in TOC -->
    <xsl:param name="generate.division.figure.lot" select="1"/>
    <xsl:param name="generate.division.table.lot" select="1"/>


    <!--
    ==================================================================================
    TABLE formatting
    - Only top and bottom borders (double style)
    - Single thin line below the heading row
    - heading font bold and sans-serif
    - No border inside the table
    ==================================================================================
    -->

    <!-- Try hard not to leave the table "open" at page breaks-->
    <xsl:attribute-set name="table.table.properties">
        <xsl:attribute name="border-after-width.conditionality">retain</xsl:attribute>
        <xsl:attribute name="border-before-width.conditionality">retain</xsl:attribute>
    </xsl:attribute-set>

    <!-- Make sure there is no break in the horizontal line below the heading 
         by setting left/right padding to zero -->
    <xsl:attribute-set name="table.cell.padding">
        <xsl:attribute name="padding-left">0pt</xsl:attribute>
        <xsl:attribute name="padding-right">0pt</xsl:attribute>
        <xsl:attribute name="padding-top">3pt</xsl:attribute>
        <xsl:attribute name="padding-bottom">4pt</xsl:attribute>
    </xsl:attribute-set>

    <!-- Add a line below the heading row and use sans-serif for heading font -->
    <xsl:template name="table.cell.block.properties">
        <!-- highlight this entry? -->
        <xsl:choose>
            <xsl:when test="ancestor::d:thead or ancestor::d:tfoot">
                <xsl:attribute name="font-weight">bold</xsl:attribute>
                <xsl:attribute name="font-family">sans-serif</xsl:attribute>
                <xsl:attribute name="color">#000000</xsl:attribute>
            </xsl:when>
            <!-- Make row headers bold too -->
            <xsl:when
                test="ancestor::d:tbody and 
                    (ancestor::d:table[@rowheader = 'firstcol'] or
                    ancestor::d:informaltable[@rowheader = 'firstcol']) and
                    ancestor-or-self::d:entry[1][count(preceding-sibling::d:entry) = 0]">
                <xsl:attribute name="font-weight">bold</xsl:attribute>
            </xsl:when>
        </xsl:choose>
        <xsl:if test="ancestor::d:thead and not(following-sibling::d:row)">
            <xsl:attribute name="border-after-width">0.5pt</xsl:attribute>
            <xsl:attribute name="border-after-style">solid</xsl:attribute>
            <xsl:attribute name="border-after-color">black</xsl:attribute>
        </xsl:if>
    </xsl:template>


    <!-- 
    ==================================================================================
    Table and figure title formatting. 
    - Center the figure
    - Put the title below, the image
    - Use italic for the title font
    - Center the title under the image
    ==================================================================================
-->

    <xsl:attribute-set name="formal.label.properties" use-attribute-sets="normal.para.spacing">
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-style">italic</xsl:attribute>
        <xsl:attribute name="font-size">
            <xsl:value-of select="$body.font.master * 1.0"/>
            <xsl:text>pt</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="color">#000000</xsl:attribute>
        <xsl:attribute name="hyphenate">false</xsl:attribute>
        <xsl:attribute name="space-after.minimum">0.4em</xsl:attribute>
        <xsl:attribute name="space-after.optimum">0.6em</xsl:attribute>
        <xsl:attribute name="space-after.maximum">0.8em</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="formal.title.properties" use-attribute-sets="normal.para.spacing">
        <xsl:attribute name="font-weight">normal</xsl:attribute>
        <xsl:attribute name="font-style">italic</xsl:attribute>
        <xsl:attribute name="font-size">
            <xsl:value-of select="$body.font.master * 1.0"/>
            <xsl:text>pt</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="hyphenate">false</xsl:attribute>
        <xsl:attribute name="space-after.minimum">0.4em</xsl:attribute>
        <xsl:attribute name="space-after.optimum">0.6em</xsl:attribute>
        <xsl:attribute name="space-after.maximum">0.8em</xsl:attribute>
    </xsl:attribute-set>

    <xsl:template name="formal.object.heading">
        <xsl:param name="object" select="."/>
        <xsl:param name="placement" select="'before'"/>
        <fo:block>
            <xsl:choose>
                <xsl:when test="$placement = 'before'">
                    <xsl:attribute name="keep-with-next.within-column">always</xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="space-after.minimum">0.6em</xsl:attribute>
                    <xsl:attribute name="space-after.optimum">0.8em</xsl:attribute>
                    <xsl:attribute name="space-after.maximum">1.2em</xsl:attribute>
                    <xsl:attribute name="keep-with-previous.within-column">always</xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
            <!-- We want the labels and titles centered before/after the figure/table -->
            <fo:block text-align="center">
                <fo:inline xsl:use-attribute-sets="formal.label.properties">
                    <xsl:call-template name="gentext">
                        <xsl:with-param name="key" select="local-name($object)"/>
                    </xsl:call-template>
                    <xsl:text> </xsl:text>
                    <xsl:apply-templates select="$object" mode="label.markup"/>
                    <xsl:text>: </xsl:text>
                </fo:inline>
                <fo:inline xsl:use-attribute-sets="formal.title.properties">
                    <xsl:apply-templates select="$object" mode="title.markup"/>
                </fo:inline>
            </fo:block>
        </fo:block>
    </xsl:template>

    <!-- Needed to center the images and tables -->
    <xsl:attribute-set name="informalfigure.properties">
        <xsl:attribute name="text-align">center</xsl:attribute>
    </xsl:attribute-set>

    <xsl:attribute-set name="figure.properties">
        <xsl:attribute name="text-align">center</xsl:attribute>
    </xsl:attribute-set>

    <!--
        Bibliography
    -->

    <xsl:template name="biblioentry.label">
        <xsl:param name="node" select="."/>
        <fo:inline font-weight="bold">
            <xsl:choose>
                <xsl:when test="$bibliography.numbered != 0">
                    <xsl:text>[</xsl:text>
                    <xsl:number from="d:bibliography" count="d:biblioentry|d:bibliomixed"
                        level="any" format="1"/>
                    <xsl:text>] </xsl:text>
                </xsl:when>
                <xsl:when test="local-name($node/child::*[1]) = 'abbrev'">
                    <xsl:text>[</xsl:text>
                    <xsl:apply-templates select="$node/d:abbrev[1]"/>
                    <xsl:text>] </xsl:text>
                </xsl:when>
                <xsl:when test="$node/@xreflabel">
                    <xsl:text>[</xsl:text>
                    <xsl:value-of select="$node/@xreflabel"/>
                    <xsl:text>] </xsl:text>
                </xsl:when>
                <xsl:when test="$node/@id or $node/@xml:id">
                    <xsl:text>[</xsl:text>
                    <xsl:value-of select="($node/@id|$node/@xml:id)[1]"/>
                    <xsl:text>] </xsl:text>
                </xsl:when>
                <xsl:otherwise><!-- nop --></xsl:otherwise>
            </xsl:choose>
        </fo:inline>
    </xsl:template>



</xsl:stylesheet>
