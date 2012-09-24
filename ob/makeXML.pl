#!/usr/bin/perl
@files = <*>;
$doc = <<EOF
<mediawiki xml:lang="en" xmlns="http://www.mediawiki.org/xml/export-0.5/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.5/ http://www.mediawiki.org/xml/export-0.5.xsd" version="0.5" >
EOF
;

foreach $file (@files)
{
	$doc.=makeXML($file);
}
$doc.="</mediawiki>\n";
print $doc;
sub makeXML()
{
	$fname = shift @_;
	
$template=<<EOF
<page>
      <title>User:Mcktimo/take1/@@title@@</title>
      <revision>
        <timestamp>2011-10-19T01:01:00Z</timestamp>
      <contributor>
        <username>Mcktimo</username>
        <id>3</id>
      </contributor>

        <text xml:space="preserve">@@page@@</text>
      </revision>
</page>
EOF
;
$/=undef;
open FILE, "$fname" or die;
$page=<FILE>;
$template=~s/@@title@@/$fname/;
$page=~s/&/&amp;/g;
$page=~s/\</&lt;/g;
$page=~s/\>/&gt;/g;

$page.="\n-------\nBased on: [[http://occupyboston.wikispaces.com/$fname $fname at wikispaces]]\n";
#$page=~s/\<br\>/\<br \/\>/g;
$template=~s/@@page@@/$page/;
return $template;
};
