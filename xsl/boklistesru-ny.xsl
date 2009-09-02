<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">

	<xsl:param name="url_ext"/>
	<xsl:param name="sortBy"/>
	<xsl:param name="order"/>
	<xsl:param name="target"/>
	<xsl:param name="showHits"/>
	<xsl:output method="html"/>

	<xsl:template match="/">
		
		<!-- ANTALL TREFF -->
		<xsl:variable name="hits">
			<xsl:value-of select="//zs:numberOfRecords"/>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="$hits=0">
				<p>Ingen treff...</p>
			</xsl:when>
			<xsl:otherwise>
			  <xsl:if test="$showHits='true'">
			  	<p>Antall treff: <xsl:value-of select="$hits"/></p>
			  </xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		
		<div class="trekkspill">
		
			<xsl:for-each>
			
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
				<xsl:choose>

			</xsl:for-each>
			
		</div>
		
	</xsl:template>

	<xsl:template name=="presentation">

				<xsl:variable name="rec" select="zs:recordData/record"/>
				<!-- Lagrer kohanr -->
				<xsl:variable name="kohanr">
					<xsl:value-of select="$rec/datafield[@tag=999]/subfield[@code='c']"/>
				</xsl:variable>
				
				<p>
				<!-- Link med tittel som navn på linken -->
				<xsl:if test="$target='remote'">
				  <a href="http://dev.bibpode.no/cgi-bin/koha/opac-detail.pl?biblionumber={$kohanr}">
				  <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a>
				</xsl:if>
				<xsl:if test="$target='local'">
				  <a href="#">
				  <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>
					<!-- Henter ut undertittel -->
					<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
					: <xsl:value-of select="."/>
					</xsl:for-each>
				  </a>
				</xsl:if>
				<!-- Skriver ut serie -->
				<xsl:if test="string-length($rec/datafield[@tag=440]/subfield[@code='a']) > 1">
					(<xsl:for-each select="$rec/datafield[@tag=440]/subfield[@code='a']">
						<xsl:choose>
							<xsl:when test="position()=last()">
								<xsl:value-of select="."/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="."/>, 
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>)
				</xsl:if>
				<br/>
				<!-- Skriver ut utgivelsesinformasjon -->
				<xsl:choose>
					<xsl:when test="(string-length($rec/datafield[@tag=260]/subfield[@code='b'])>3)
												and(string-length($rec/datafield[@tag=260]/subfield[@code='c'])>3)">
						<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>, 
						<xsl:value-of select="translate($rec/datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')"/>
					</xsl:when>
					<xsl:otherwise>
							<xsl:if test="string-length($rec/datafield[@tag=260]/subfield[@code='b'])>3">
								<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>
							</xsl:if>
							<xsl:if test="string-length($rec/datafield[@tag=260]/subfield[@code='c'])>3">
								<xsl:value-of select="translate($rec/datafield[@tag=260]/subfield[@code='c'], 'cop.[]', '')"/>
							</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				</p>
			
			<xsl:if test="$target='local'">	
			<p>
			<!-- ### KOPI FRA boksru.xsl -->				
				<!-- Henter 245 $c -->
				Opplysninger: <xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='c']"/>, 
				<!-- Henter 300 $a -->
			<xsl:value-of select="$rec/datafield[@tag=300]/subfield[@code='a']"/>
			<br/>
			<!-- Henter ut forfatter fra post 100 $a og skriver den ut hvis den er større enn 0 -->
			<xsl:if test="(string-length($rec/datafield[@tag=100]/subfield[@code='a'])>0)
					or(string-length($rec/datafield[@tag=700]/subfield[@code='a'])>0)">
					Medvirkende: 
					<xsl:if test="string-length($rec/datafield[@tag=100]/subfield[@code='a'])>0">
					<xsl:value-of select="$rec/datafield[@tag=100]/subfield[@code='a']"/>
					<br/>
				</xsl:if>
				<!-- Henter ut post 700 $a og $e -->
				<xsl:for-each select="$rec/datafield[@tag=700]">
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
				Utgitt: <xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='a']"/>, 
				<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='b']"/>, 
				<xsl:value-of select="$rec/datafield[@tag=260]/subfield[@code='c']"/>
			<br/>
			<!-- Utgave -->
			<xsl:if test="string-length($rec/datafield[@tag=250]/subfield[@code='a'])>0">
					Utgave: <xsl:value-of select="$rec/datafield[@tag=250]/subfield[@code='a']"/>
				<br/>
			</xsl:if>
			<!-- Skriver ut språk -->
			<xsl:if test="string-length($rec/datafield[@tag=041]/subfield[@code='h'])>0">
					Språk: <xsl:value-of select="$rec/datafield[@tag=041]/subfield[@code='h']"/>
				<br/>
			</xsl:if>
			<!-- Skriver ut ISBN hvis den er større enn 0 -->
			<xsl:variable name="isbn">
				<xsl:value-of select="$rec/datafield[@tag=020]/subfield[@code='a']"/>
			</xsl:variable>
			<xsl:if test="string-length($isbn)>0">
					ISBN: <xsl:value-of select="$isbn"/>
				<br/>
			</xsl:if>
			<!-- Skriver ut emner -->
			<xsl:if test="string-length($rec/datafield[@tag=650])>0">
					Emner: 
				<xsl:for-each select="$rec/datafield[@tag=650]">
					<xsl:value-of select="subfield[@code='a']"/>
					<xsl:if test="string-length(subfield[@code='x'])>0">
						<xsl:text>, </xsl:text>
						<xsl:value-of select="subfield[@code='x']"/>
					</xsl:if>
					<br/>
				</xsl:for-each>
			</xsl:if>
			<a href="http://torfeus.deich.folkebibl.no/cgi-bin/koha/opac-detail.pl?biblionumber={$kohanr}">Vis i katalogen</a>
			<!-- Skriver ut omslag fra Open Library hvis det finnes 
			<xsl:if test="string-length($isbn)>0">
				<xsl:variable name="imgisbn">
					<xsl:value-of select="translate($isbn, '-', '')"/>
				</xsl:variable>
				<img alt="" src="http://covers.openlibrary.org/b/isbn/{$imgisbn}-M.jpg"/>
			</xsl:if>
			-->
			
			<!-- ### SLUTT på kopi fra boksru.xsl ### -->
			</p>
			</xsl:if>
		
	</xsl:template>
</xsl:stylesheet>
