<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:variable name="currentPage" select="data/params/returned_page"/>
	<xsl:variable name="sid" select="data/params/sourceID"/>
	<xsl:variable name="lo" select="data/params/loc"/>
	<xsl:variable name="sulo" select="data/params/subloc"/>
	<xsl:template match="/">
		<xsl:for-each select="data/aList">
			<select NAME="selSource" SIZE="1" ONCHANGE="changeSource(this.value);">
				<option>select an article</option>
				<xsl:for-each select="article">
					<option>
						<xsl:attribute name="value"><xsl:value-of select="source"/></xsl:attribute> 
						<xsl:value-of select="source"/>
					</option>
				</xsl:for-each>
			</select>
		</xsl:for-each>
		<xsl:call-template name="menu"/>			
		<xsl:for-each select="data/grid/words">
			<table id="words">
				<tbody>
				<xsl:element name="tr">   
					<td class="word"><xsl:value-of select="word" /></td>
					<td class="aid">
						<xsl:value-of select="wid" /> 
					</td>
				</xsl:element>
				<tr>
					<xsl:for-each select="defs">
						<table id="defs">   
							<xsl:element name="tr">	
								<xsl:attribute name="id">
									dtr<xsl:value-of select="did" />
								</xsl:attribute>
								<xsl:attribute name="class">trdef</xsl:attribute>
								<td><xsl:value-of select="def" /></td>
								<td class="aid">
									<xsl:value-of select="did"/>
								</td>
								<xsl:element name="td">
									<xsl:attribute name="id">defbutton</xsl:attribute>
									<xsl:element name="a">
										<xsl:attribute name = "href">#</xsl:attribute>
										<xsl:attribute name = "onclick">
											editDef(<xsl:value-of select="did" />, true)
										</xsl:attribute>
										Edit Def
										<br/>
									</xsl:element>
									<xsl:element name="a">
										<xsl:attribute name = "href">#</xsl:attribute>
										<xsl:attribute name = "onclick">
											deleteId(<xsl:value-of select="did" />, <xsl:value-of select="$currentPage"/>)
										</xsl:attribute>
										Del Def
									</xsl:element>
								</xsl:element>
							</xsl:element>
							<tr>As Used in Academic Discourse
							</tr>
							<tr>	
								<xsl:for-each select="contexts">
									<xsl:element name="table">
										<xsl:attribute name="id">
											ctbl
										</xsl:attribute>	
										<xsl:element name="tr">
											<xsl:attribute name="id">
												ctr<xsl:value-of select="cid" />
											</xsl:attribute>
											<xsl:attribute name="class">trcon</xsl:attribute>
											<td><xsl:value-of select="sentence" /></td>
											<td><xsl:value-of select="pos"/></td>
											<td><xsl:value-of select="sourceID"/>.<xsl:value-of select="loc"/>.<xsl:value-of select="subloc"/></td>
											<td><xsl:value-of select="cid"/></td>
											<xsl:element name="td">
												<xsl:attribute name="id">contextbutton</xsl:attribute>
												<xsl:element name="a">
													<xsl:attribute name = "href">#</xsl:attribute>
													<xsl:attribute name = "onclick">
														editContext(<xsl:value-of select="cid" />, true)
													</xsl:attribute>
													Edit Context
												</xsl:element>
											</xsl:element>
										</xsl:element>
									</xsl:element>
								</xsl:for-each>
							</tr>
							<tr>
								Uses in Your Peer Discourse
							</tr>
							<tr>
								<xsl:for-each select="youruses">
									<table id="ytbl">
										<xsl:element name="tr">
											<xsl:attribute name="id">
												ytr<xsl:value-of select="yid" />
											</xsl:attribute>
											<xsl:attribute name="class">tryou</xsl:attribute>
											<td><xsl:value-of select="yourdef" /></td>
											<td><xsl:value-of select="youruse"/></td>
											<td><xsl:value-of select="yourID"/></td>
											<td><xsl:value-of select="block"/></td>
											<td><xsl:value-of select="yourOK"/></td>
											<td><xsl:value-of select="rating"/></td>
											<xsl:element name="td">
												<xsl:attribute name="id">yourbutton</xsl:attribute>
												<xsl:element name="a">
													<xsl:attribute name = "href">#</xsl:attribute>
													<xsl:attribute name = "onclick">
														editYour(<xsl:value-of select="yid" />, true)
													</xsl:attribute>
													Edit Your
												</xsl:element>
											</xsl:element>
										</xsl:element>
									</table>
								</xsl:for-each>
							</tr>
							<tr>
								<xsl:element name="td">
									<xsl:attribute name="id">addyour</xsl:attribute>
									<xsl:element name="a">
										<xsl:attribute name = "href">#</xsl:attribute>
										<xsl:attribute name = "onclick">
											addYour(<xsl:value-of select="did"/>,  <xsl:value-of select="$currentPage"/>)
										</xsl:attribute>
										Add Your
									</xsl:element>
								</xsl:element>
							</tr>
						</table>
					</xsl:for-each>
				</tr>
			</tbody>
			</table>
		</xsl:for-each>
		<xsl:call-template name="menu" /> 
	</xsl:template>
	<xsl:template name="menu">
		<xsl:for-each select="data/params">
			<table class="nav">
				<tr>
					<td class="left">
						<xsl:value-of select="items_count" /> Items
					</td> 
					<td class="right"> 
						<xsl:choose>
							<xsl:when test="previous_page>0">
								<xsl:element name="a" >
									<xsl:attribute name="href" >#</xsl:attribute>
									<xsl:attribute name="onclick">
										loadGridPage(<xsl:value-of select="previous_page"/>)
									</xsl:attribute>
									Previous page
								</xsl:element>
							</xsl:when> 
						</xsl:choose>
					</td>   
					<td class="left">
						<xsl:choose>
							<xsl:when test="next_page>0">
								<xsl:element name="a">
									<xsl:attribute name = "href" >#</xsl:attribute>
									<xsl:attribute name = "onclick">
										loadGridPage(<xsl:value-of select="next_page"/>)
									</xsl:attribute>
									Next page
								</xsl:element>
							</xsl:when> 
						</xsl:choose>
					</td>
					<td class="right">
						page <xsl:value-of select="returned_page" />
						of <xsl:value-of select="total_pages" />
					</td>  
				</tr>
			</table>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
