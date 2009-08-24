<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:param name="url_ext"/>
	<xsl:param name="sortBy"/>
	<xsl:param name="order"/>
	<xsl:param name="target"/>
	<xsl:output method="html"/>
	<xsl:template match="/">
		<xsl:if test="$sortBy='title'">
			<xsl:for-each select="//record">
				<xsl:sort select="datafield[@tag=245]/subfield[@code='a']" data-type="text" order="{$order}"/>
				<!-- Lagrer tittelnr -->
				<xsl:variable name="tittelnr">
					<xsl:value-of select="controlfield[@tag=001]"/>
				</xsl:variable>
				<!-- Link med tittel som navn på linken -->
				<xsl:if test="$target='remote'">
				  <a href="http://www.deich.folkebibl.no/cgi-bin/websok?mode=p&amp;st=p&amp;tnr={$tittelnr}">
				  <xsl:value-of select="datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<xsl:if test="$target='local'">
				  <a href="?tittelnr={$tittelnr}{$url_ext}">
				  <xsl:value-of select="datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<!-- Skriver ut utgivelsesinformasjon -->
				<xsl:choose>
					<xsl:when test="((string-length(datafield[@tag=260]/subfield[@code='b'])>0)
												and(string-length(datafield[@tag=260]/subfield[@code='c'])>0))">
						<xsl:value-of select="datafield[@tag=260]/subfield[@code='b']"/>, 
						<xsl:value-of select="datafield[@tag=260]/subfield[@code='c']"/>
						<br/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="string-length(datafield[@tag=260]/subfield[@code='b'])>0">
								<xsl:value-of select="datafield[@tag=260]/subfield[@code='b']"/>
								<br/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="datafield[@tag=260]/subfield[@code='c']"/>
								<br/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
				<br/>
			</xsl:for-each>
		</xsl:if>
		<xsl:if test="$sortBy='year'">
			<xsl:for-each select="//record">
				<xsl:sort select="translate(datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')" data-type="number" order="{$order}"/>
				<!-- Lagrer tittelnr -->
				<xsl:variable name="tittelnr">
					<xsl:value-of select="controlfield[@tag=001]"/>
				</xsl:variable>
				<!-- Link med tittel som navn på linken -->
				<xsl:if test="$target='remote'">
				  <a href="http://www.deich.folkebibl.no/cgi-bin/websok?mode=p&amp;st=p&amp;tnr={$tittelnr}">
					<xsl:value-of select="datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<xsl:if test="$target='local'">
				  <a href="?tittelnr={$tittelnr}{$url_ext}">
				  <xsl:value-of select="datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a><br/>
				</xsl:if>
				<!-- Skriver ut utgivelsesinformasjon -->
				<xsl:choose>
					<xsl:when test="(string-length(datafield[@tag=260]/subfield[@code='b'])>0)
												and(string-length(datafield[@tag=260]/subfield[@code='c'])>0)">
						<xsl:value-of select="datafield[@tag=260]/subfield[@code='b']"/>, 
						<xsl:value-of select="datafield[@tag=260]/subfield[@code='c']"/>
						<br/>
					</xsl:when>
					<xsl:otherwise>
							<xsl:if test="string-length(datafield[@tag=260]/subfield[@code='b'])>0">
								<xsl:value-of select="datafield[@tag=260]/subfield[@code='b']"/>
								<br/>
							</xsl:if>
							<xsl:if test="string-length(datafield[@tag=260]/subfield[@code='c'])>0">
								<xsl:value-of select="datafield[@tag=260]/subfield[@code='c']"/>
								<br/>
							</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				<br/>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
