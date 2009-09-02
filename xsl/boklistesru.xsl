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
	
	<!-- ANTALL TREFF -->
	<xsl:variable name="hits">
		<xsl:value-of select="//zs:numberOfRecords"/>
	</xsl:variable>
	<xsl:choose>
		<!-- Vis altid 0 treff -->
		<xsl:when test="$hits=0">
			<p>Ingen treff...</p>
		</xsl:when>
		<xsl:otherwise>
			<!-- Vis antall terff bare dersom det spørres etter det. -->
		  <xsl:if test="$showHits='true'">
		  	<p>Antall treff: <xsl:value-of select="$hits"/></p>
		  </xsl:if>
		</xsl:otherwise>
	</xsl:choose>
	
	<xsl:choose>
		<xsl:when test="$sortBy='title'">
			<xsl:apply-templates select="//zs:record">
				<!-- Sorter etter hovedtittel, deretter undertittel, deretter år -->
				<xsl:sort select="zs:recordData/record/datafield[@tag=245]/subfield[@code='a']" data-type="text" order="{$order}"/>
				<xsl:sort select="zs:recordData/record/datafield[@tag=245]/subfield[@code='b']" data-type="text" order="{$order}"/>
				<xsl:sort select="translate(zs:recordData/record/datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="descending"/>
			</xsl:apply-templates>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates select="//zs:record">
				<!-- Sorter etter år, etter at "cop." og "[]" er fjernet. -->
				<xsl:sort select="translate(zs:recordData/record/datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="{$order}"/>
			</xsl:apply-templates>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template match="//zs:record">

		<!-- Lagrer kohanr -->
		<xsl:variable name="rec" select="zs:recordData/record"/>
		<xsl:variable name="kohanr">
			<xsl:value-of select="$rec/datafield[@tag=999]/subfield[@code='c']"/>
		</xsl:variable>
	
		<xsl:choose>
			<xsl:when test="$target='remote'">
				<div class="trekkspill">
					<xsl:call-template name="ekstern-lenke-trekkspill">
						<xsl:with-param name="kohanr" select="$kohanr"/>
						<xsl:with-param name="rec" select="$rec"/>
					</xsl:call-template>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="intern-lenke">
					<xsl:with-param name="kohanr" select="$kohanr"/>
					<xsl:with-param name="rec" select="$rec"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>

</xsl:template>

<xsl:template name="intern-lenke">

	<xsl:param name="kohanr"/>
	<xsl:param name="rec"/>

	<p>
	<a href="?tittelnr={$kohanr}{$url_ext}">
	<xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
	<!-- Henter ut undertittel -->
	<xsl:call-template name="undertittel">
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

	<xsl:param name="kohanr"/>
	<xsl:param name="rec"/>

	<p>
	<a href="#">
	<xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
	<!-- Henter ut undertittel -->
	<xsl:call-template name="undertittel">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	</a>
	<!-- Skriver ut forfatter -->
	<xsl:if test="$visForfatter">
		<xsl:if test="string-length($rec/datafield[@tag=100]/subfield[@code='a'])>0">
		<xsl:value-of select="$rec/datafield[@tag=100]/subfield[@code='a']"/>
		</xsl:if>
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
		<xsl:with-param name="kohanr" select="$kohanr"/>
		<xsl:with-param name="visBilde" select="0"/>
	</xsl:call-template>
	<br />
	<a href="http://torfeus.deich.folkebibl.no/cgi-bin/koha/opac-detail.pl?biblionumber={$kohanr}">Vis i katalogen</a>
	</p>
	
</xsl:template>

</xsl:stylesheet>
