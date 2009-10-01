<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">

<xsl:param name="url_ext"/>
<xsl:param name="sortBy"/>
<xsl:param name="order"/>
<xsl:param name="target"/>
<xsl:param name="showHits"/>
<xsl:param name="visForfatter"/>
<xsl:param name="hits"/>
<xsl:param name="querystring"/>
<xsl:param name="page"/>
<xsl:param name="perPage"/>
<xsl:output method="html"/>

<xsl:include href="felles.xsl"/>

<xsl:template match="/">

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
			<xsl:apply-templates select="//record">
				<!-- Sorter etter hovedtittel, deretter undertittel, deretter år -->
				<xsl:sort select="datafield[@tag=245]/subfield[@code='a']" data-type="text" order="{$order}"/>
				<xsl:sort select="datafield[@tag=245]/subfield[@code='b']" data-type="text" order="{$order}"/>
				<xsl:sort select="translate(datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="descending"/>
				<xsl:with-param name="first" select="$first"/>
				<xsl:with-param name="last" select="$last"/>
			</xsl:apply-templates>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates select="//record">
				<!-- Sorter etter år, etter at "cop." og "[]" er fjernet. -->
				<xsl:sort select="translate(datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="{$order}"/>
				<xsl:with-param name="first" select="$first"/>
				<xsl:with-param name="last" select="$last"/>
			</xsl:apply-templates>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template match="//record"> 

	<xsl:param name="first"/>
	<xsl:param name="last"/>

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
			<!-- Vis bare de postene som er innenfor det intervallet vi skal se -->
			<xsl:if test="position() &gt;= $first and position() &lt;= $last">
				<xsl:call-template name="intern-lenke">
					<xsl:with-param name="rec" select="$rec"/>
					<xsl:with-param name="tittelnr" select="$tittelnr"/>
				</xsl:call-template>
			</xsl:if>
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