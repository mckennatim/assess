#!/usr/bin/perl

$/=undef;
$output = <>;

  $output =~s/\r\n/\n/g;

#  output = breakMulti('**', output);
#  output = breakMulti('//', output);


  $output =~s/\*\*(.*?)\*\*/'''$1'''/g;
  $output =~s/(^|[^:])\/\/(.*?)\/\//$1''$2''/g;
  $output =~s/__(.*?)__/<u>$1<\/u>/g;
  $output =~s/\{\{(.*?)\}\}/<tt>$1<\/tt>/g;

  $output =~s/\[\[code\]\](.*?)\[\[code\]\]/<code>$1<\/code>/;
  $output =~s/\[\[math\]\](.*?)\[\[math\]\]/<math>$1<\/math>/;

  $output =~s/\[\[toc\]\]/__TOC__/i;
# we don't get this stuff
#  output = varReplace(['REVISIONID','REVISIONDAY','REVISIONDAY2','REVISIONMONTH','REVISIONYEAR','REVISIONTIMESTAMP','SERVER','SERVERNAME','PAGE','FULLPAGENAME'], output);
#  $output =~s/{{\$SERVERNAME}}/{{\$SERVER}}/;
#  $output =~s/{{\$PAGE}}/{{\$PAGENAME}}');

#  $output =~s/\[\[include\s+page="([^"]+)"\s*editable="true"/g, '[[include page="$1"');
#  $output =~s/\[\[include\s+page="([^"]+)"\s*title="([^"]+)"/g, '==$2==\n[[include page="$1"');
#  $output =~s/\[\[include\s+page="([^"]+)"\s*component="comments"\s*\]\]/g, '{{:Talk:$1}}');
#  $output =~s/\[\[include\s+page="([^"]+)"\s*component="backlinks"\s*\]\]/g, '{{:Special:Whatlinkshere/$1}}');
#  $output =~s/\[\[include\s+page="([^"]+)"\s*\]\]/g, '{{:$1}}');


#  // Links
  $output =~s/\[\[@?(https?:\/\/[^\|]+)\|([^\]]+)\]\]/[$1 $2]/g;
  $output =~s/(^|[^\[])(@?https?:\S+)/$1\[$2 $2\]/g;
  $output =~s/(^|[^\[])([\w\.]+)@([\w\.]+)/$1\[mailto:$2@$3 $2@$3\]/g;

 # // Images. Widtgh and Height are discarded.
  $output =~s/\[\[media.*?\]\]//g; # no idea what to do with these -- mdw

  $output =~s/\[\[image:([^\]]+?)\s+align\s*=\s*['"]?(left|right|center)['"]?/[[image:$1|$2/g;
  $output =~s/\[\[image:([^\]]+?)\s+link\s*=\s*['"]([^'"]+)['"]/[[image:$1/g;
  $output =~s/\[\[image:([^\]]+?)\s+caption\s*=\s*['"]([^'"]+)['"]/[[image:$1|$2/g;
  $output =~s/\[\[image:([^\]]+?)\s+width\s*=\s*['"]?\d+['"]?/[[image:$1/g;
  $output =~s/\[\[image:([^\]]+?)\s+height\s*=\s*['"]?\d+['"]?/[[image:$1/g;


  #// Convert tables
  $output =~s/(^|[^\|][^\|]\n)(\|\|)/$1\{|\n|-\n$2/g;
  $output =~s/\|\|(\n[^\|][^\|]|$)/||\n|\}$1/g;

  $output =~s/\s*\|\|\n\|\|/\n|-\n||/g;
  $output =~s/\s*\|\|\n\|}/\n|}/g;

#  $output =~s/( *[^\n])\|\|/$1\n||"); // Separate cells
  $output =~s/\|\|~/!!/g;
  $output =~s/\|\|!!/||||/g;
  $output =~s/\|\|=/|| align=center |/g;
  $output =~s/\|\|>/|| align=right |/g;
  $output =~s/\|\|</|| align=left |/g;
  $output =~s/\|{8}/|| colspan=4 |/g;
  $output =~s/\|{6}/|| colspan=3 |/g;
  $output =~s/\|{4}/|| colspan=2 |/g;
  $output =~s/colspan=(\d+) \| align=(right|left|center)/colspan=$1 align=$2/g;
  $output =~s/align=(right|left|center) \| colspan=(\d+)/align=$1 colspan=$2/g;

#  // Header conversion
  $output =~s/\|\| align=center \| '''(.+?)'''/!! $1/g;

  $output =~s/\n\|\|/\n|/g;
  $output =~s/\n!!/\n!/g;

# // Add line breaks. Often the most troublesome formatting between wikispace and mediawiki
  $output =~s/\n/<br>\n/g;
  $output =~s/<br>\n<br>\n/\n\n/g;

#  // Remove br from list and tables
  $output =~s/(?:^|\n)(\*\s+.*?)<br>\n/\n$1\n/g;
  $output =~s/<br>\n(\*\s+)/\n$1/g;
  $output =~s/(?:^|\n)(#\s+.*?)<br>\n/\n$1\n/g;
  $output =~s/<br>\n(#\s+)/\n$1/g;
  $output =~s/(?:^|\n)(\|.*?)<br>\n/\n$1\n/g;
  $output =~s/<br>\n({\|)/\n$1/g;
  $output =~s/(?:^|\n)(=+.*?)<br>\n/\n$1\n/g;
  $output =~s/<br>\n(=+)/\n$1/g;
  $output =~s/(?:^|\n)({\||\|-|\|})<br>\n/\n$1\n/g;
  $output =~s/(?:^|\n)(!|\|)(.+?)<br>\n/\n$1$2\n/g;

print $output;