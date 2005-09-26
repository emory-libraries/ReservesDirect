#!/usr/bin/perl -w


use Net::Z3950;
use strict;

use CGI qw(:standard escapeHTML);
use CGI::Carp qw(fatalsToBrowser);

use MARC::XML;
#use MARC::Charset;
print header( -TYPE => 'text/xml');

my $host = param('host');
my $port = param('port');
my $db = param('db');
my $query = param('query');
my $start = param('start');
my $limit = param('limit');
my $marc;
my $xmlMarc;
my $uTime = time();
my $rand = rand(100);
my $tmp = "/tmp/marc.$uTime.$rand.mrc";
my $marcRec;
my $leader;
my $dtd = "http://arachnot.library.emory.edu/xml/dtd/ansel_unicode.ent";
my $conn = new Net::Z3950::Connection($host, $port, databaseName => $db)
    or die "can't connect: $!";
#my $charset = MARC::Charset->new();
$conn->option(preferredRecordSyntax => Net::Z3950::RecordSyntax::USMARC);

#while (@ARGV) {
#    my $type = shift();
#    my $val = shift();
#    $conn->option($type, $val);
#}

my $rs = $conn->search($query)
    or die $conn->errmsg();

my $n = $rs->size();
if($n < $limit) {
	$limit = $n;
}
$limit = $start + $limit;
$rs->present(($start + 1), $limit) or die "failed: $rs->{errcode}\n";
for (my $i = $start; $i < $limit; $i++) {
    my $rec = $rs->record($i+1);
    if (!defined $rec) {
	print STDERR "record ", $i+1, ": error #", $rs->errcode(),
	    " (", $rs->errmsg(), "): ", $rs->addinfo(), "\n";
	next;
    }

    $marc = $marc . $rec->rawdata();

}
    open(TMPFILE, ">$tmp") || die "Cannot open temp file!\n";
    print TMPFILE $marc;
    close TMPFILE;

    $xmlMarc = MARC::XML->new($tmp, "usmarc");
    my $charset = $xmlMarc->ansel_default;
  
    print $xmlMarc->output_header({'lineterm'=>'', 'dtd_file'=>$dtd});
	print "<recordCount>$n</recordCount>";
    print $xmlMarc->output_body({lineterm=>'', 'dtd_file'=>$dtd});

    print $xmlMarc->output_footer({'lineterm'=>'', 'dtd_file'=>$dtd});
unlink($tmp);
