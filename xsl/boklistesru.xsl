<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">

<xsl:param name="url_ext"/>
<xsl:param name="sortBy"/>
<xsl:param name="order"/>
<xsl:param name="target"/>
<xsl:param name="showHits"/>
<xsl:param name="visForfatter"/>
<xsl:param name="querystring"/>
<xsl:param name="page"/>
<xsl:param name="perPage"/>
<xsl:param name="limit"/>
<xsl:output method="html"/>

<xsl:include href="felles.xsl"/>

<xsl:template match="/">
	
	<!-- ANTALL TREFF -->
	<xsl:variable name="hits">
		<xsl:choose>
			<xsl:when test="number(//zs:numberOfRecords) &lt;= $limit">
				<xsl:value-of select="number(//zs:numberOfRecords)"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$limit"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<xsl:choose>
		<!-- Vis altid 0 treff -->
		<xsl:when test="$hits=0">
			<p>Ingen treff...</p>
		</xsl:when>
		<xsl:otherwise>
			<!-- Vis antall treff bare dersom det spørres etter det. -->
		  <xsl:if test="$showHits='true'">
		  	<p>Antall treff: <xsl:value-of select="$hits"/></p>
		  </xsl:if>
		</xsl:otherwise>
	</xsl:choose>
	
	<!-- Variabler for første og siste post som skal vises -->
	<xsl:variable name="first"><xsl:value-of select="(($page - 1) * $perPage) + 1"/></xsl:variable>
	<xsl:variable name="last">
		<xsl:choose>
			<xsl:when test="($page * $perPage) &gt; $hits">
				<xsl:value-of select="$hits"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$page * $perPage"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<xsl:choose>
		<xsl:when test="$target='local'">
			<!-- "Viser treff x - y av z" -->
			<p>Viser treff <xsl:value-of select="$first"/> - <xsl:value-of select="$last"/> av <xsl:value-of select="$hits"/></p>
			<!-- Navigasjon neste/forrige side -->
			<p>
				<xsl:choose>
					<xsl:when test="$page=1">Forrige side</xsl:when>
					<xsl:otherwise>
						<a>
						<xsl:attribute name="href">?
							<xsl:value-of select="substring-before($querystring, 'ZZZ')"/>
							<xsl:value-of select="$page - 1"/>
							<xsl:value-of select="substring-after($querystring, 'ZZZ')"/>
						</xsl:attribute>
						Forrige side</a>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:text> | </xsl:text>
				<xsl:choose>
					<xsl:when test="(($page + 1) * $perPage) &gt; $hits + $perPage">Neste side</xsl:when>
					<xsl:otherwise>
					<a>
					<xsl:attribute name="href">?
						<xsl:value-of select="substring-before($querystring, 'ZZZ')"/>
						<xsl:value-of select="$page + 1"/>
						<xsl:value-of select="substring-after($querystring, 'ZZZ')"/>
					</xsl:attribute>
					Neste side</a>
					</xsl:otherwise>
				</xsl:choose>
			</p>
		</xsl:when>
	</xsl:choose>
	
	<xsl:choose>
		<xsl:when test="$sortBy='title'">
			<xsl:apply-templates select="//zs:record">
				<!-- Sorter etter hovedtittel, deretter undertittel, deretter år -->
				<xsl:sort select="zs:recordData/record/datafield[@tag=245]/subfield[@code='a']" data-type="text" order="{$order}"/>
				<xsl:sort select="zs:recordData/record/datafield[@tag=245]/subfield[@code='b']" data-type="text" order="{$order}"/>
				<xsl:sort select="translate(zs:recordData/record/datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="descending"/>
				<xsl:with-param name="first" select="$first"/>
				<xsl:with-param name="last" select="$last"/>
			</xsl:apply-templates>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates select="//zs:record"> 
				<!-- Sorter etter år, etter at "cop." og "[]" er fjernet. -->
				<xsl:sort select="translate(zs:recordData/record/datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="{$order}"/>
				<xsl:with-param name="first" select="$first"/>
				<xsl:with-param name="last" select="$last"/>
			</xsl:apply-templates>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template match="//zs:record">

		<xsl:param name="first"/>
		<xsl:param name="last"/>

		<!-- Lagrer kohanr -->
		<xsl:variable name="rec" select="zs:recordData/record"/>
		<xsl:variable name="kohanr">
			<xsl:value-of select="$rec/datafield[@tag=999]/subfield[@code='c']"/>
		</xsl:variable>
	
		<xsl:choose>

			<!-- Poster i bokser på høyre side -->
			<xsl:when test="$target='remote'">
				<div class="trekkspill">
					<xsl:call-template name="ekstern-lenke-trekkspill">
						<xsl:with-param name="kohanr" select="$kohanr"/>
						<xsl:with-param name="rec" select="$rec"/>
					</xsl:call-template>
				</div>
			</xsl:when>
			
			<!-- Poster i hovedvisningen på venstre side -->
			<xsl:otherwise>
				<!-- Vis bare de postene som er innenfor det intervallet vi skal se -->
				<xsl:if test="position() &gt;= $first and position() &lt;= $last">
					<xsl:call-template name="intern-lenke">
						<xsl:with-param name="kohanr" select="$kohanr"/>
						<xsl:with-param name="rec" select="$rec"/>
					</xsl:call-template>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>

</xsl:template>

<xsl:template name="intern-lenke">

	<xsl:param name="kohanr"/>
	<xsl:param name="rec"/>

	<p>
	<a href="?tittelnr={$kohanr}{$url_ext}">
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

	<xsl:param name="kohanr"/>
	<xsl:param name="rec"/>

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
		<xsl:with-param name="kohanr" select="$kohanr"/>
		<xsl:with-param name="visBilde" select="0"/>
	</xsl:call-template>
	<br />
	<a href="{$item_url}{$kohanr}">Vis i katalogen</a>
	</p>
	
</xsl:template>

</xsl:stylesheet>
