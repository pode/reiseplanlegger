<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:zs="http://www.loc.gov/zing/srw/">

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

<xsl:template name="tittel_undertittel">

	<xsl:param name="rec"/>
	<xsl:call-template name="tittel">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>
	<xsl:call-template name="undertittel">
		<xsl:with-param name="rec" select="$rec"/>
	</xsl:call-template>

</xsl:template>


<xsl:template name="tittel">

	<xsl:param name="rec"/>
	<xsl:value-of select="$rec/datafield[@tag=245]/subfield[@code='a']"/>

</xsl:template>

<xsl:template name="undertittel">

	<xsl:param name="rec"/>
	<xsl:for-each select="$rec/datafield[@tag=245]/subfield[@code='b']">
		: <xsl:value-of select="."/>
	</xsl:for-each>

</xsl:template>

<xsl:template name="forfatter">

	<xsl:param name="rec"/>
	<xsl:if test="string-length($rec/datafield[@tag=100]/subfield[@code='a'])>0">
		av <xsl:value-of select="$rec/datafield[@tag=100]/subfield[@code='a']"/>
	</xsl:if>

</xsl:template>

<xsl:template name="serie">

	<xsl:param name="rec"/>
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

</xsl:template>

<xsl:template name="utgivelse">

	<xsl:param name="rec"/>
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

</xsl:template>

<xsl:template name="detaljer">

	<xsl:param name="rec"/>
	<xsl:param name="visBilde"/>

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
	
</xsl:template>

<xsl:template name="visForsideBilde">

	<xsl:param name="rec"/>

	<xsl:variable name="isbn">
		<xsl:value-of select="$rec/datafield[@tag=020]/subfield[@code='a']"/>
	</xsl:variable>
	<xsl:if test="string-length($isbn)>0">
		<br /><br /><img alt="" src="api/image.php?isbn={$isbn}"/>
	</xsl:if>

</xsl:template>

</xsl:stylesheet>