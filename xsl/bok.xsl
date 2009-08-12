<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="html"/>
	<xsl:template match="/">
		<xsl:for-each select="//record">
			<!-- Henter ut url til Deichmanske -->
			<xsl:variable name="url">
				<xsl:value-of select="datafield[@tag=996]/subfield[@code='u']"/>
			</xsl:variable>
			<strong>
				<!-- Henter ut tittel -->
				<xsl:value-of select="datafield[@tag=245]/subfield[@code='a']"/>
				<!-- Henter ut undertittel -->
				<xsl:for-each select="datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
				</xsl:for-each>
			</strong>
			<br/>
			<!-- Henter 245 $c -->
				Opplysninger: <xsl:value-of select="datafield[@tag=245]/subfield[@code='c']"/>, 
				<!-- Henter 300 $a -->
			<xsl:value-of select="datafield[@tag=300]/subfield[@code='a']"/>
			<br/>
			<!-- Henter ut forfatter fra post 100 $a og skriver den ut hvis den er større enn 0 -->
			<xsl:if test="(string-length(datafield[@tag=100]/subfield[@code='a'])>0)
					or(string-length(datafield[@tag=700]/subfield[@code='a'])>0)">
					Medvirkende: 
					<xsl:if test="string-length(datafield[@tag=100]/subfield[@code='a'])>0">
					<xsl:value-of select="datafield[@tag=100]/subfield[@code='a']"/>
					<br/>
				</xsl:if>
				<!-- Henter ut post 700 $a og $e -->
				<xsl:for-each select="datafield[@tag=700]">
					<xsl:if test="string-length(subfield[@code='a'])>0">
						<xsl:value-of select="subfield[@code='a']"/>
						<xsl:if test="string-length(subfield[@code='e'])>0">
								(<xsl:value-of select="subfield[@code='e']"/>)
							</xsl:if>
						<br/>
					</xsl:if>
				</xsl:for-each>
			</xsl:if>
			<!-- Skriver ut utgivelsesinformasjon -->
				Utgitt: <xsl:value-of select="datafield[@tag=260]/subfield[@code='a']"/>, 
				<xsl:value-of select="datafield[@tag=260]/subfield[@code='b']"/>, 
				<xsl:value-of select="datafield[@tag=260]/subfield[@code='c']"/>
			<br/>
			<!-- Utgave -->
			<xsl:if test="string-length(datafield[@tag=250]/subfield[@code='a'])>0">
					Utgave: <xsl:value-of select="datafield[@tag=250]/subfield[@code='a']"/>
				<br/>
			</xsl:if>
			<!-- Skriver ut språk -->
			<xsl:if test="string-length(datafield[@tag=041]/subfield[@code='h'])>0">
					Språk: <xsl:value-of select="datafield[@tag=041]/subfield[@code='h']"/>
				<br/>
			</xsl:if>
			<!-- Skriver ut ISBN hvis den er større enn 0 -->
			<xsl:variable name="isbn">
				<xsl:value-of select="datafield[@tag=020]/subfield[@code='a']"/>
			</xsl:variable>
			<xsl:if test="string-length($isbn)>0">
					ISBN: <xsl:value-of select="$isbn"/>
				<br/>
			</xsl:if>
			<!-- Skriver ut emner -->
			<xsl:if test="string-length(datafield[@tag=650])>0">
					Emner: 
				</xsl:if>
			<xsl:for-each select="datafield[@tag=650]">
				<xsl:value-of select="subfield[@code='a']"/>
				<xsl:if test="string-length(subfield[@code='x'])>0">
					<xsl:text>, </xsl:text>
					<xsl:value-of select="subfield[@code='x']"/>
				</xsl:if>
				<br/>
			</xsl:for-each>
			<a href="{$url}">Link til Deichmanske bibliotek</a>
			<br/>
			<br/>
			<!-- Skriver ut omslag fra Open Library hvis det finnes -->
			<xsl:if test="string-length($isbn)>0">
				<xsl:variable name="imgisbn">
					<xsl:value-of select="translate($isbn, '-', '')"/>
				</xsl:variable>
				<img alt="" src="http://covers.openlibrary.org/b/isbn/{$imgisbn}-M.jpg"/>
			</xsl:if>
			<br/>
			<br/>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
