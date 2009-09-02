<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">
	
	<xsl:output method="html"/>
	<xsl:include href="felles.xsl"/>
	
	<xsl:template match="/">
		<xsl:for-each select="//zs:record">

			<p>		
			<xsl:variable name="rec" select="zs:recordData/record"/>
			<xsl:variable name="kohanr">
				<xsl:value-of select="$rec/datafield[@tag=999]/subfield[@code='c']"/>
			</xsl:variable>
			<strong>
				<!-- Henter ut tittel-->
				<xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
				<!-- Henter ut undertittel -->
				<xsl:call-template name="undertittel">
					<xsl:with-param name="rec" select="$rec"/>
				</xsl:call-template>
			</strong>
			
			<xsl:call-template name="detaljer">
				<xsl:with-param name="rec" select="$rec"/>
				<xsl:with-param name="kohanr" select="$kohanr"/>
				<xsl:with-param name="visBilde" select="1"/>
			</xsl:call-template>
			
			<br />
			<a href="http://torfeus.deich.folkebibl.no/cgi-bin/koha/opac-detail.pl?biblionumber={$kohanr}">Vis i katalogen</a>
			
			<xsl:call-template name="visForsideBilde">
				<xsl:with-param name="rec" select="$rec"/>
			</xsl:call-template>
			</p>
			
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
