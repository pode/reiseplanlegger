<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	
	<xsl:output method="html"/>
	<xsl:include href="felles.xsl"/>
	
	<xsl:template match="/">
		<xsl:for-each select="//record">
		
			<p>	
			<xsl:variable name="rec" select="//record"/>
			<strong>
				<!-- Henter ut tittel og undertittel -->
				<xsl:call-template name="tittel_undertittel">
					<xsl:with-param name="rec" select="$rec"/>
				</xsl:call-template>
			</strong>
			<br />
			
			<xsl:call-template name="detaljer">
				<xsl:with-param name="rec" select="$rec"/>
				<xsl:with-param name="visBilde" select="1"/>
			</xsl:call-template>
			<br />
			
			<!-- Henter ut url til Deichmanske -->
			<xsl:variable name="url">
				<xsl:value-of select="datafield[@tag=996]/subfield[@code='u']"/>
			</xsl:variable>
			<a href="{$url}">Vis i katalogen</a>
			
			<xsl:call-template name="visForsideBilde">
				<xsl:with-param name="rec" select="$rec"/>
			</xsl:call-template>
			</p>
			
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
