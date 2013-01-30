<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html"/>

	<xsl:template match="/">
		<table>
			<thead>
				<tr>
					<th></th>
					<th>Команда</th>
					<th>Игры</th>
					<th>Победы</th>
					<th>Ничьи</th>
					<th>Проигрыши</th>
					<th>Мячи</th>
					<th>Очки</th>
				</tr>
			</thead>
			<tbody class="teams">
				<xsl:apply-templates select="//tr[position() > 1]" />
			</tbody>
		</table>
	</xsl:template>
	
	<xsl:template match="//tr[position() > 1]">
		<xsl:variable name="rowclass">
			<xsl:choose>
				<xsl:when test="position() mod 2 = 0">even</xsl:when>
				<xsl:otherwise>odd</xsl:otherwise>
			</xsl:choose>			
		</xsl:variable>
		
		<tr class="{$rowclass}">
			<td class="position"><xsl:number value="position()" /></td>
			<td class="name"><xsl:value-of select="td[3]/a" /></td>
			<td class="games"><xsl:value-of select="td[4]" /></td>
			<td class="wins"><xsl:value-of select="td[5]" /></td>
			<td class="draws"><xsl:value-of select="td[6]" /></td>
			<td class="losses"><xsl:value-of select="td[7]" /></td>
			<td class="balls"><xsl:value-of select="translate(td[8], '- ', '—')" /></td>
			<td class="points"><xsl:value-of select="td[9]" /></td>
		</tr>
	</xsl:template>
	
</xsl:stylesheet>