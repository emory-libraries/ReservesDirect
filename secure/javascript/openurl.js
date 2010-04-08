/** openURL javascript functions for addDigitalItem and editItem **/

// OpenURL Item object constructor
// Includes elements of a standard openurl 1.0 object query
function OpenURLItem() 
{
  this.atitle = "";
  this.jtitle = "";
  this.stitle = "";
  this.date = "";
  this.issn = "";
  this.eissn = "";
  this.coden = "";
  this.volume = "";
  this.issue = "";
  this.sici = "";
  this.spage = "";
  this.epage = "";
  this.part = "";
  this.artnum = "";
  this.ssn = "";
  this.quarter = "";
  this.aulast = "";
  this.aufirst = "";
  this.auinitm = "";
  this.auinit = "";
  this.auinit1 = "";
  this.ausuffix = "";
  this.au = "";
  this.aucorp = "";
  this.pages = "";
  this.btitle = "";
  this.isbn = "";
  this.genre = "";
  this.chron = "";
  this.svc_fulltext = "";
  this.svc_citation = "";
  this.svc_holdings = "";
  this.svc_ill = "";
  this.svc_abstract = "";
  this.rft_id = "";
}

// Opens a standard popup window for links on the site.
function sfxWin(mypage) {

  var winw = 640;
  var winh = 480;
  var winl = (screen.width - winw) / 2;
  var wint = (screen.height - winh) / 2;

  var winprops = 'height='+winh+',width='+winw+',top='+wint+',left='+winl+',toolbar=no,scrollbars=yes,resizable'

  SFXwin = window.open(mypage, 'sfxwin', winprops);
  
  if (parseInt(navigator.appVersion) >= 4) { SFXwin.window.focus(); }
}

// using the meta data text fields on the form, get an open url
function get_url(frm) {
  var alertMsg = '';
  var sURL = "http://sfx.galib.uga.edu/sfx_emu1";
  var url_ver = "Z39.88-2004"; 
  var rft_val_fmt = "info:ofi/fmt:kev:mtx:journal";
  
  var obj = new OpenURLItem();

  sURL += "?url_ver=" + escape(url_ver); 
  sURL += "&rft_val_fmt=" + escape(rft_val_fmt);
  
  obj["title"] = frm.title.value;
  obj = getAuthorInfo(obj, frm.author.value);
  obj["volume_title"] = frm.volume_title.value;
  obj = getVolumeInfo(obj, frm.volume_edition.value);
  obj = getYearInfo(obj, frm.source.value);
  obj = getPageInfo(obj, frm.times_pages.value);           
  obj["ISBN"] = frm.ISBN.value;
  obj["ISSN"] = frm.ISSN.value;
          
  // Output all the fields for debugging
  alertMsg += "Title[" +   frm.title.value + "]<br>";
  alertMsg += "Author[" +   frm.author.value + "] => ";
  alertMsg += "First[" + obj["aufirst"] + "] ";
  alertMsg += "MI[" + obj["auinitm"] + "] ";
  alertMsg += "Last[" + obj["aulast"] + "] ";
  alertMsg += "<br>";
  alertMsg += "Journal[" +   frm.volume_title.value + "]<br>";
  alertMsg += "Volume/Issue[" +   frm.volume_edition.value + "] => "
  alertMsg += "Vol[" + obj["volume"] + "] ";
  alertMsg += "Iss[" + obj["issue"] + "] ";
  alertMsg += "Num[" + obj["artnum"] + "] ";  
  alertMsg += "<br>";
  alertMsg += "Year of Publication[" +   frm.source.value + "] => Year[" + obj["date"] + "]<br>";
  alertMsg += "PageRange[" +   frm.times_pages.value + "] => Start Page[" + obj["spage"] + "] End Page[" + obj["epage"] + "]<br>"; 
  alertMsg += "ISSN[" +   frm.ISSN.value + "]<br>";
  alertMsg += "ISBN[" +   frm.ISBN.value + "]<br>";
  
  var sQuery = BuildQueryString(obj);
  var sOpenURL = sURL + sQuery; 
  alertMsg = sOpenURL;
   
  return alertMsg;
}

// Extracts the author
function getAuthorInfo (obj, auinfo)
{
  var pat = [];
  pat[0]="^[\\s]*([A-Za-z'\\-]+),[\\s]*([A-Z])([A-Z])[,\\s]*et al[\\.\\s]*$"; // "(Last), (FI)(MI) et al."   
  pat[1]="^[\\s]*([A-Za-z'\\-]+),[\\s]*([A-Za-z'\\-]+)[,\\s]*et al[\\.\\s]*$"; // "(Last), (First) et al."
  pat[2]="^[\\s]*([A-Za-z'\\-]+)[\\s]+et al\\.?$"; // "(Last) et al." 
  pat[3]="^[\\s]*([A-Za-z'\\-]+),[\\s]*([A-Za-z'\\-]+)[\\.\\s]+([A-Z])[A-Za-z'\\-]*[\\.\\s]*"; // "(Last), (First) (Middle)"   
  pat[4]="^[\\s]*([A-Za-z'\\-]+),?[\\s]*([A-Z])([A-Z])[,\\s]*"; // "(Last), (FI)(MI)"
  pat[5]="^[\\s]*([A-Za-z'\\-]+)[\\s]+([A-Za-z'\\-]+)[\\s]*$"; // "(First) (Last)" 
  pat[6]="^[\\s]*([A-Za-z'\\-]+),[\\s]*([A-Za-z'\\-]+)[\\.\\s]*(and|;)[\\s]*"; // "(Last), (First) and" 
  pat[7]="^[\\s]*([A-Za-z'\\-]+),[\\s]*([A-Za-z'\\-]+),[\\s]*([A-Za-z'\\-]+)"; // "(Last), (Last), (Last)"
  pat[8]="^[\\s]*([A-Za-z'\\-]+)[\\s]*&[\\s]*([A-Za-z'\\-]+)[\\s]*$"; // "(Last) & (Last)"
  pat[9]="^[\\s]*([A-Za-z'\\-]+)[\\s]+([A-Z])[\\.\\s]+([A-Za-z'\\-]+)"; // "(First) (MI) (Last)" 
  pat[10]="^[\\s]*([A-Za-z'\\-]+)[\\s]+([A-Za-z'\\-]+)[\\.\\s]+and[\\s]*"; // "(First) (Last) and"  
  pat[11]="^[\\s]*([A-Za-z'\\-]+)[,\\s]+([A-Za-z'\\-]+)[,\\s]*"; // "(Last), (FI)"  
  pat[12]="^[\\s]*([A-Za-z'\\-]+)[\\s]*$"; // "(Last)"  
 
  for(i=0; i<pat.length; i++){ 
    var regex = new RegExp(pat[i]);
    var m = regex.exec(auinfo);
    
    if( m != null ) {
      switch(i)
      {
      case 0: // Contains last, first and mi
      case 3:
      case 4: obj["aulast"] = m[1]; obj["aufirst"] = m[2];  obj["auinitm"] = m[3];  break;
      
      case 1: // Contains last and first
      case 6: 
      case 11: obj["aulast"] = m[1]; obj["aufirst"] = m[2];  break;

      case 2: // Contains last only
      case 7: 
      case 8:  
      case 12: obj["aulast"] = m[1]; break; 
      
      case 5: // Contains first last
      case 10: obj["aufirst"] = m[1];  obj["aulast"] = m[2]; break; 
      
              // Contains first last
      case 9: obj["aufirst"] = m[1];  obj["auinitm"] = m[2];   obj["aulast"] = m[3];  break; 
      }    
      break;
    }    
  }         
  return obj;  
}

// Extracts the start and end page from a string
function getPageInfo (obj, pageinfo)
{
  var regex = new RegExp("([0-9]+)*-*([0-9]+)");
  var m = regex.exec(pageinfo);
  
  if( m != null ) {
    obj["spage"] = m[1];  // Start page
    obj["epage"] = m[2];  // End page
  }

  return obj;
}

// Extracts the volume/issue/number 
function getVolumeInfo (obj, volinfo)
{ 
  var pat = [];
  // volume = 2nd group; issue = 4th group.
  pat[0]="^([\\s]*)([0-9]+)([\\s]*\/[\\s]*)([0-9]+)[\\s]*$"; // 5/8     
  pat[1]="^([\\s]*)([0-9]+)[\\s]*\\(([\\s]*)([0-9]+)[\\s]*\\)[\\s]*$"; // 5(8)
  pat[2]="^[\\s]*(Volume|Vol|V)[\\s\\.]+([0-9]+)[\\s,\\:]*(Iss|Issue)[\\.\\s]*([0-9]+)[\\s]*$"; // Vol. 234, Issue 3   
  // volume = 2nd group; number = 4th group.     
  pat[3]="^[\\s]*(Volume|Vol|V)[\\.\\s]*([0-9]+)[\\s,\\:]*(Numbers|No)[\\.\\s]*([0-9\\-]+)[\\s]*"; // Vol. 234 , No. 3 
  // number = 2nd group;  
  pat[4]="^[\\s]*(Numbers|No)[\\.\\s]*([0-9\\-]+)[\\s]*";  // No. 12 or Numbers 23-26
  // volume = 2nd group;
  pat[5]="^[\\s]*(Volume|Vol|V)[\\.\\s]*([0-9]+)[\\s,]*"; // Vol. 234 
  pat[6]="^([\\s]*)([0-9]+)[\\s]*";  // 12 
  
  for(i=0; i<pat.length; i++){ 
    var regex = new RegExp(pat[i], "i");
    var m = regex.exec(volinfo);
    
    if( m != null ) {
      switch(i)
      {
      case 0: obj["volume"] = m[2]; obj["issue"] = m[4];  break;
      case 1: obj["volume"] = m[2]; obj["issue"] = m[4];  break;
      case 2: obj["volume"] = m[2]; obj["issue"] = m[4];  break;
      case 3: obj["volume"] = m[2]; obj["artnum"] = m[4]; break;
      case 4: obj["artnum"] = m[2]; break;
      case 5: obj["volume"] = m[2]; break;
      case 6: obj["volume"] = m[2]; break;  
      case 7: obj["volume"] = m[0]; break;       
      }        
      break;
    }    
  } 
  return obj;
}

// Extracts the year 
function getYearInfo (obj, yrinfo)
{
  var regex = new RegExp("(\\d{4})");
  var m = regex.exec(yrinfo);
  
  if( m != null ) {
    obj["date"] = m[1];   
  } 
  return obj;
}

// Builds the query string portion of defined openurl query items.
function BuildQueryString (obj)
{
  sString = "";
  for (var piece in obj)
  {
    if(obj[piece] != "" && obj[piece])
    {
        if(piece.substring(0,3) != "rft")
        {
        sString += "&rft." + piece + "=" + escape(obj[piece]);
      } else {
        sString += "&" + piece + "=" + escape(obj[piece]);
      }
    }
  }
  return sString;
}
