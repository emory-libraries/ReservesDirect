#!/usr/bin/perl -wT

##############################################################################
# rss2js.pl                                                                  #
#                                                                            #
# This program writes out an RSS file to JavaScript for remote display       #
#                                                                            #
# by Nik Jewell. v0.2 20th May 2002                                          #
#                                                                            #
# Configuration of the visual display characteristics can be carried out     #
# with the accompanying rssconfig.pl script                                  #
#                                                                            #
# Please contact L.N.Jewell@leeds.ac.uk with bugfixes, suggested             #
# improvments or for assistance                                              #
#                                                                            #
# Copyright (C) 2002 PRS-LTSN                                                #
#                                                                            #
# This program is free software; you can redistribute it and/or              #
# modify it under the terms of the GNU General Public License                #
# as published by the Free Software Foundation; either version 2             #
# of the License, or (at your option) any later version.                     #
#                                                                            #
# This program is distributed in the hope that it will be useful,            #
# but WITHOUT ANY WARRANTY; without even the implied warranty of             #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              #
# GNU General Public License for more details.                               #
#                                                                            #
# You should have received a copy of the GNU General Public License          #
# along with this program; if not, write to the Free Software                # 
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA #
##############################################################################


# Modules

use strict;

use CGI;
use LWP::Simple;
use XML::RSS;

# Declare global variables

my ($width,$height,$listOn,$listOff);

# Create an instance of CGI

my $query = new CGI;
my $pathToCss = "/home/httpd/html/styles/";
#####################################################################

# Collect the query data

my $remote = $query->param('remote');
my $name = $query->param('name');
my $nameDesc = $query->param('nameDesc');
my $image = $query->param('image');
my $desc = $query->param('desc');
my $num = $query->param('num');
my $box = $query->param('box');
my $copyr = $query->param('copyr');
my $date = $query->param('date');
my $list = $query->param('list');
my $wid = $query->param('wid');
my $css = $query->param('style');
my $ci = $query->param('ci');

#####################################################################

# Create an instance of XML::RSS
if (not defined $num) { $num = 1000; }
if (not defined $remote) { $remote = "http://biliku.library.emory.edu/jbwhite/projects/reserves2_1/rss.php?ci=$ci"; }

if (not defined $name) { $name = "y"; }
if (not defined $nameDesc) { $nameDesc = "y"; }
if (not defined $desc) { $desc = "y"; }
if (not defined $wid) { $wid = "100%"; }

my $rss = new XML::RSS;
# Fetch the remote file
$remote =~ s/and/\&/g;
$remote =~ s/equals/\=/g;
$remote =~ s/question/\?/g;

#print $remote;

my $xml = get($remote);

# Parse the retrieved file

$rss->parse($xml);

# Create the JavaScript

&OUTPUT($rss);

#####################################################################

# Main display 

sub OUTPUT {

	if (not defined $wid) { $wid = '200' }			
	
	# Display news items as list items?
	
	if (defined($list)) {
		$listOn = "<ul class=\"rssList\"><li>";
		$listOff = "</li></ul>";
	}
				
	# Print the header

	#print "Content-type: text/html\n\n";
	# Print the opening container table tags
	if($css) {

		open(CSS, "$pathToCss/$css.css");
		my $x = 0;
		while(<CSS>) {
			print "document.write('" . quotemeta($_) . "')\n";
		}

	}
		
		
	print "document.write('<table class=\"rssTable\" cellspacing=\"0\" cellpadding=\"2\" width=\"$wid\">')\n";

	# Call the individual display subroutines

	if (defined($name)) {
		&NAME;
	}
	if ($rss->{'image'}->{'link'} && defined($image)) {
		&IMAGE;
	}
	if ($rss->{'channel'}->{'description'} && defined($nameDesc)) {
		&NAMEDESC;
	}
 	if (defined($desc)) {
		&DESCRIPTION;
	}
	else {
		&TITLE;
	}
	if (($rss->{'textinput'}->{'title'}) && defined($box)) {
		&TEXTINPUT;
	}
	if (($rss->{'channel'}->{'pubDate'}) && defined($date)) {
		&PUBDATE;
	}
	if (($rss->{'channel'}->{'copyright'}) && defined($copyr)) {
		&COPYRIGHT;
	}
	
	# Print the closing container table tags
	
	print "document.write('</td></tr></table>')\n";

	# Force JavaScript interpreter to write contents immediately
	
	print "document.close()\n";
}

#####################################################################

# Print channel name

sub NAME {
	print "document.write('<tr class=\"rssChan\"><td valign=\"middle\" align=\"center\">')\n";
	my $chan="<a class=\"rssLink\" href=\"$rss->{'channel'}->{'link'}\">$rss->{'channel'}->{'title'}</a>";
	&PRINT($chan);
	print "document.write('</td></tr>')\n";
}

#####################################################################

# Print channel image

sub IMAGE {
	if ($rss->{'image'}->{'width'}) {
		$width = "$rss->{'image'}->{'width'}";
	}
	if ($rss->{'image'}->{'height'}) {
		$height = "$rss->{'image'}->{'height'}";
	}
	print "document.write('<tr class=\"rssImage\"><td><center><p><a href=\"$rss->{'image'}->{'link'}\"><img src=\"$rss->{'image'}->{'url'}\" alt=\"$rss->{'image'}->{'title'}\" border=\"0\" width=\"$width\" height=\"$height\"></a></center><p></td></tr>')\n";
}

#####################################################################

# Print channel description

sub NAMEDESC {
	print "document.write('<tr class=\"rssChanDesc\"><td valign=\"middle\" align=\"center\">')\n";
	my $chandesc="$rss->{'channel'}->{'description'}";
	&PRINT($chandesc);
	print "document.write('</td></tr>')\n";
}

#####################################################################

# Print item title

sub TITLE {
	
	if (not defined $num) { $num = '0'; }
	my $s = 1;
	if ($list eq "y") { 
		my $tableOpen = "<tr class=\"rssItem\"><td><ul class=\"rssList\">";
		my $tableClose = "</ul></td></tr>";
		&PRINT($tableOpen);
		foreach my $items (@{$rss->{'items'}}) {
			next unless defined($items->{'title'}) && ($s <= $num);
			my $titles;
			if(defined($items->{'link'})) {
				$titles = "<li><a class=\"rssLink\" href=\"$items->{'link'}\">$items->{'title'}</a></li><br />";
			} else {
				$titles = "<li>$items->{'title'}</li><br />";
			}
			&PRINT($titles);
			$s++
		}
		&PRINT($tableClose);
	}																																														
	else {
		foreach my $items (@{$rss->{'items'}}) {
			next unless defined($items->{'title'}) && ($s <= $num);
			my $titles;
			if(defined($items->{'link'})) {
				$titles = "<tr class=\"rssItem\"><td><a class=\"rssLink\" href=\"$items->{'link'}\">$items->{'title'}</a></td></tr>";
			} else {
				$titles = "<tr class=\"rssItem\"><td>$items->{'title'}</td></tr>";
			}
			&PRINT($titles);
			$s++
		}
	}

}

#####################################################################

# Print item title and description

sub DESCRIPTION {	
	
	if (not defined $listOn) { $listOn = ''; }
	if (not defined $listOff) { $listOff = ''; }
	if (not defined $num) { $num = '0'; }
			
	my $s = 1;
	foreach my $items (@{$rss->{'items'}}) {
		next unless defined($items->{'title'})  && ($s <= $num);
		my $title;
		if(defined($items->{'link'})) {
			$title = "<tr class=\"rssItem\"><td>$listOn<a class=\"rssLink\" href=\"$items->{'link'}\">$items->{'title'}</a>$listOff</td></tr>";
		} else {
			#my $title = "<tr class=\"rssItem\"><td>$listOn$items->{'title'}\$listOff</td></tr>";
			$title = "<tr class=\"rssItem\"><td>$items->{'title'}</td></tr>";
		}
		my $desc = "<tr class=\"rssDesc\"><td>$items->{'description'}</td></tr>";
		&PRINT($title);	
		&PRINT($desc);
		$s++;
	}
}

#####################################################################

# Print channel textinput box
    
sub TEXTINPUT {
	my $input = "<tr class=\"rssTextInput\"><td><center><form method=\"get\" action=\"$rss->{'textinput'}->{'link'}\">$rss->{'textinput'}->{'description'}<br /><input type=\"text\" name=\"$rss->{'textinput'}->{'name'}\"><br /><input type=\"submit\" value=\"$rss->{'textinput'}->{'title'}\"></form></center></td></tr>";
	&PRINT($input);
}

#####################################################################

# Print channel publication date
	
sub PUBDATE {
	my $pub="<tr class=\"rssPubDate\"><td><center>$rss->{'channel'}->{'pubDate'}</center></td></tr>";
	&PRINT($pub);
}

#####################################################################

# Print channel copyright

sub COPYRIGHT {
	my $copyR = "<tr class=\"rssCopyR\" id=\"rssCopyR\"><td><center>$rss->{'channel'}->{'copyright'}</center></td></tr>";
		&PRINT($copyR);
}

#####################################################################

# Clean up RSS input before printing

sub PRINT {

	# Escape any single quotes
	
	$_[0] =~ s/\'/\\'/g;
	
	# Get rid of any stray new lines, form feeds or carriage returns in the input
	
	$_[0] =~ s/\n//g;
	$_[0] =~ s/\f//g;
	$_[0] =~ s/\r//g;

	# Print the output

	print "document.write('$_[0]')\n";	
}


