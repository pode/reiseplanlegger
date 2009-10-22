<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<!--

Copyright 2009 ABM-utvikling

This file is part of "Podes reiseplanlegger".

"Podes reiseplanlegger" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

"Podes reiseplanlegger" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with "Podes reiseplanlegger".  If not, see <http://www.gnu.org/licenses/>.

Source code available from: 
http://github.com/pode/reiseplanlegger/

-->

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
			
			<!-- Henter ut url til posten i katalogen -->
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
