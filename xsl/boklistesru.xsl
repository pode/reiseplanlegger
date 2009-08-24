<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">
	<xsl:param name="url_ext"/>
	<xsl:param name="sortBy"/>
	<xsl:param name="order"/>
	<xsl:param name="target"/>
	<xsl:param name="showHits"/>
	<xsl:output method="html"/>
	<xsl:template match="/">
		<xsl:variable name="hits">
			<xsl:value-of select="//zs:numberOfRecords"/>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="$hits=0">
				<p>Ingen treff...</p>
			</xsl:when>
			<xsl:otherwise>
			  <xsl:if test="$showHits='true'">
			  	Antall treff: <xsl:value-of select="$hits"/>
				<br/>
				<br/>
			  </xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="$sortBy='title'">
			<xsl:for-each select="//zs:record">
				<xsl:sort select="zs:recordData/record/datafield[@tag=245]/subfield[@code='a']" data-type="text" order="{$order}"/>
				<xsl:variable name="rec" select="zs:recordData/record"/>
				<!-- Lagrer kohanr -->
				<xsl:variable name="kohanr">
					<xsl:value-of select="$rec/datafield[@tag=999]/subfield[@code='c']"/>
				</xsl:variable>
				<!-- Link med tittel som navn på linken -->
				<xsl:if test="$target='remote'">
				  <a href="http://dev.bibpode.no/cgi-bin/koha/opac-detail.pl?biblionumber={$kohanr}">
				  <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<xsl:if test="$target='local'">
				  <a href="?tittelnr={$kohanr}{$url_ext}">
				  <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<!-- Skriver ut utgivelsesinformasjon -->
				<xsl:choose>
					<xsl:when test="(string-length($rec/datafield[@tag=260]/subfield[@code='b'])>3)
												and(string-length($rec/datafield[@tag=260]/subfield[@code='c'])>3)">
						<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>, 
						<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='c']"/>
						<br/>
					</xsl:when>
					<xsl:otherwise>
							<xsl:if test="string-length($rec/datafield[@tag=260]/subfield[@code='b'])>3">
								<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>
								<br/>
							</xsl:if>
							<xsl:if test="string-length($rec/datafield[@tag=260]/subfield[@code='c'])>3">
								<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='c']"/>
								<br/>
							</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				<br/>
			</xsl:for-each>
		</xsl:if>
		<xsl:if test="$sortBy='year'">
			<xsl:for-each select="//zs:record">
				<xsl:sort select="zs:recordData/record/datafield[@tag=260]/subfield[@code='c']" data-type="number" order="{$order}"/>
				<xsl:variable name="rec" select="zs:recordData/record"/>
				<!-- Lagrer kohanr -->
				<xsl:variable name="kohanr">
					<xsl:value-of select="$rec/datafield[@tag=999]/subfield[@code='c']"/>
				</xsl:variable>
				<!-- Link med tittel som navn på linken -->
				<xsl:if test="$target='remote'">
				  <a href="http://dev.bibpode.no/cgi-bin/koha/opac-detail.pl?biblionumber={$kohanr}">
				  <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<xsl:if test="$target='local'">
				  <a href="?tittelnr={$kohanr}{$url_ext}">
				  <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<!-- Skriver ut utgivelsesinformasjon -->
				<xsl:choose>
					<xsl:when test="(string-length($rec/datafield[@tag=260]/subfield[@code='b'])>3)
												and(string-length($rec/datafield[@tag=260]/subfield[@code='c'])>3)">
						<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>, 
						<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='c']"/>
						<br/>
					</xsl:when>
					<xsl:otherwise>
							<xsl:if test="string-length($rec/datafield[@tag=260]/subfield[@code='b'])>3">
								<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>
								<br/>
							</xsl:if>
							<xsl:if test="string-length($rec/datafield[@tag=260]/subfield[@code='c'])>3">
								<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='c']"/>
								<br/>
							</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				<br/>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
