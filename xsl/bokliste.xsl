<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">

<xsl:param name="url_ext"/>
<xsl:param name="sortBy"/>
<xsl:param name="order"/>
<xsl:param name="target"/>
<xsl:param name="showHits"/>
<xsl:param name="visForfatter"/>
<xsl:output method="html"/>

<xsl:include href="felles.xsl"/>

<xsl:template match="/">
	
	<xsl:choose>
		<xsl:when test="$sortBy='title'">
			<xsl:apply-templates select="//record">
				<!-- Sorter etter hovedtittel, deretter undertittel, deretter år -->
				<xsl:sort select="datafield[@tag=245]/subfield[@code='a']" data-type="text" order="{$order}"/>
				<xsl:sort select="datafield[@tag=245]/subfield[@code='b']" data-type="text" order="{$order}"/>
				<xsl:sort select="translate(datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="descending"/>
			</xsl:apply-templates>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates select="//record">
				<!-- Sorter etter år, etter at "cop." og "[]" er fjernet. -->
				<xsl:sort select="translate(datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="{$order}"/>
			</xsl:apply-templates>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template match="//record"> 

	<xsl:variable name="rec" select="."/>
	<!-- Lagrer tittelnr -->
	<xsl:variable name="tittelnr">
		<xsl:value-of select="$rec/controlfield[@tag=001]"/>
	</xsl:variable>
	<xsl:choose>
		<xsl:when test="$target='remote'">
			<div class="trekkspill">
				<xsl:call-template name="ekstern-lenke-trekkspill">
					<xsl:with-param name="rec" select="$rec"/>
					<xsl:with-param name="tittelnr" select="$tittelnr"/>
				</xsl:call-template>
			</div>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="intern-lenke">
				<xsl:with-param name="rec" select="$rec"/>
				<xsl:with-param name="tittelnr" select="$tittelnr"/>
			</xsl:call-template>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template name="intern-lenke">

	<xsl:param name="rec"/>
	<xsl:param name="tittelnr"/>

	<p>
	<a href="?tittelnr={$tittelnr}{$url_ext}">
	<!-- Henter ut tittel og undertittel -->
	<xsl:call-template name="tittel_undertittel">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	</a>
	<!-- Skriver ut serie -->
	<xsl:call-template name="serie">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	<br />
	<!-- Skriver ut utgivelsesinformasjon -->
	<xsl:call-template name="utgivelse">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	</p>

</xsl:template>

<xsl:template name="ekstern-lenke-trekkspill">

	<xsl:param name="rec"/> 
	<xsl:param name="tittelnr"/>

	<p>
	<a href="#">
	<!-- Henter ut tittel og undertittel -->
	<xsl:call-template name="tittel_undertittel">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	</a>
	<!-- Skriver ut forfatter -->
	<xsl:if test="$visForfatter">
		<xsl:call-template name="forfatter">
			<xsl:with-param name="rec" select="$rec"/>
		</xsl:call-template>
	</xsl:if>
	<!-- Skriver ut serie -->
	<xsl:call-template name="serie">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	<br/>
	<!-- Skriver ut utgivelsesinformasjon -->
	<xsl:call-template name="utgivelse">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	</p>
	<!-- Skriver ut detaljer -->
	<p>
	<xsl:call-template name="detaljer">
		<xsl:with-param name="rec" select="$rec"/>
		<xsl:with-param name="kohanr" select="$tittelnr"/>
		<xsl:with-param name="visBilde" select="0"/>
	</xsl:call-template>
	<br />
	<!-- Henter ut url til Deichmanske -->
	<xsl:variable name="url">
		<xsl:value-of select="$rec/datafield[@tag=996]/subfield[@code='u']"/>
	</xsl:variable>
	<a href="{$url}">Vis i katalogen</a>
	</p>
	
</xsl:template>

</xsl:stylesheet>