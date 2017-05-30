#!/usr/bin/perl

# 
#  Akwan Information Technologies
#
#  Base Grouper (grouper.pl) v. 1.1
#
#  Groups bases spread over several directories
#  into a single base by appending the files
#

#
# Changes list
#
# Golgher 05/11/2002 : Fixed bug in grouping html and metatags and link 
# Golgher 04/06/2002 : Added support to linkcontent.
# Golgher 02/04/2002 : Added support to html.
# Golgher 27/03/2002 : Added support to prio and date. Drop abstract

use Cwd;

my ($base_name, $home,$robots_base_name);
my ($out_text, $out_idx, $out_geral, $out_geral_idx, $out_size, $out_id, $out_prio, $out_date, $out_title, $out_title_idx, $out_type, $out_type_idx);
my ($count_text, $offset_text, $count_geral, $offset_geral, $count_title, $offset_title, $count_type, $offset_type, $count_meta, $offset_meta, $count_html, $offset_html,$count_link, $offset_link  );
my ($out_text_standard, $out_idx_standard, $out_geral_standard, $out_geral_idx_standard, $out_size_standard, $out_id_standard, $out_prio_standard, $out_date_standard, $out_title_standard, $out_title_idx_standard, $out_type_standard, $out_type_idx_standard);
my ($file_size, $file_geral_size, $file_title_size, $file_type_size, $file_size_size, $file_id_size, $file_prio_size, $file_date_size, $file_meta_size, $file_html_size,$file_link_size  );
my ($file_size_idx, $file_geral_size_idx, $file_title_size_idx, $file_type_size_idx, $file_meta_size_idx,$file_html_size_idx,$file_link_size_idx  );

my ($size_limit);
my ($count_dir, $text);
my ($file_count, $file_count_prox, $file_offset, $file_offset_prox);
my ($file_geral_count, $file_geral_count_prox, $file_geral_offset, $file_geral_offset_prox); 
my ($file_title_count, $file_title_count_prox, $file_title_offset, $file_title_offset_prox); 
my ($file_type_count, $file_type_count_prox, $file_type_offset, $file_type_offset_prox); 
my ($numero_lidos, $elementos);
my ($VERBOSE_MODE);

#  ERROR CODES
#  
#  $ERROR_CODES[0] Can´t open file
#  $ERROR_CODES[1] Can´t access directory
#
my @ERROR_CODES = ("01000","01001"); 


if (@ARGV < 1){
  die "Usage :\ngrouper.pl --version | [-v verbose mode] <robots> <collection> <home> [dirs]\n";
}


#Catches the --version flag
if ($ARGV[0] eq "--version") {

  print "-------------------------------------------------------\n";
  print "Akwan Information Technologies   \n";
  print "http://www.akwan.com.br   \n";
  print "   \n";
  print "   \n";
  print "                    Base Grouper (v. 1.1)    \n";
  print "-------------------------------------------------------\n";
  exit(0);

}



#if ARGV[0] equals to -v switch to verbose mode and the 
#arguments index should be incremented

if ($ARGV[0] eq "-v") {

  $VERBOSE_MODE = 1;

} else {

  $VERBOSE_MODE = 0;	

} 

$robots_base_name = $ARGV[0+$VERBOSE_MODE];
$base_name = $ARGV[1+$VERBOSE_MODE];
$home = $ARGV[2+$VERBOSE_MODE];

$size_limit = 2**31;
 
if (not chdir ($home)) {

	print $ERROR_CODES[1].": Can't access  $home $!\n";
	exit(-1);

}

$out_idx = $base_name."text.idx";

$out_geral_idx = $base_name."geral.idx";

$out_id = $base_name."id";

$out_prio = $base_name."prio";

$out_date = $base_name."date";

$out_meta_idx = $base_name."meta.idx";

$out_html_idx = $base_name."html.idx";

$out_link_idx = $base_name."linkcontent.idx";


if (not open IDXFILE, "+>>$out_idx"){
	print "$ERROR_CODES[0]: Can't open file $out_idx: $!\n";
	exit(-1);
}

if (not open GERAL_IDXFILE, "+>>$out_geral_idx"){
	print "$ERROR_CODES[0]: Can't open file $out_geral_idx: $!\n";
	exit(-1);
}
if (not open ID_OUTFILE, "+>>$out_id"){
	print "$ERROR_CODES[0]: Can't open file $out_id: $!\n";
	exit(-1);
}
if (not open PRIO_OUTFILE, "+>>$out_prio"){
	print "$ERROR_CODES[0]: Can't open file $out_prio: $!\n";
	exit(-1);
}
if (not open DATE_OUTFILE, "+>>$out_date"){
	print "$ERROR_CODES[0]: Can't open file $out_date: $!\n";
	exit(-1);
}

if (not open META_IDXFILE, "+>>$out_meta_idx") {
	print "$ERROR_CODES[0]: Can't open file $out_meta_idx: $!\n";
	exit(-1);
}
if (not open HTML_IDXFILE, "+>>$out_html_idx") {
	print "$ERROR_CODES[0]: Can't open file $out_html_idx: $!\n";
	exit(-1);
}

if (not open LINK_IDXFILE, "+>>$out_link_idx") {
	print "$ERROR_CODES[0]: Can't open file $out_link_idx: $!\n";
	exit(-1);
}



&Init_Count_Offset (\IDXFILE, "text", \$count_text, \$offset_text);
&Init_Count_Offset (\GERAL_IDXFILE, "geral", \$count_geral, \$offset_geral);
&Init_Count_Offset (\META_IDXFILE, "meta", \$count_meta, \$offset_meta);
&Init_Count_Offset (\HTML_IDXFILE, "html", \$count_html, \$offset_html);
&Init_Count_Offset (\LINK_IDXFILE, "linkcontent", \$count_link, \$offset_link);

&Init_Id_Size (\ID_OUTFILE, "id");
&Init_Id_Size (\PRIO_OUTFILE, "prio");
&Init_Id_Size (\DATE_OUTFILE, "date");

$out_text = $base_name."text".$count_text;

if ($VERBOSE_MODE) {

	print "Current Directory". $ENV{PWD}. "\n";

} 


$out_geral = $base_name."geral".$count_geral;


$out_meta = $base_name."meta".$count_meta;

$out_html = $base_name."html".$count_html;

$out_link = $base_name."linkcontent".$count_link;

if (not open TEXTFILE, "+>>$out_text"  ) {

	print "$ERROR_CODES[0]: Can't open file $out_text: $!\n";
	exit(-1);

}

if ($VERBOSE_MODE) {

	print "Current Text File $out_text\n"; 

} 

if (not open GERAL_OUTFILE, ">>$out_geral" ) {

	print "$ERROR_CODES[0]: Can't open file $out_geral: $!\n";
	exit(-1);

}

if (not open META_OUTFILE, ">>$out_meta"  ) {

	print "$ERROR_CODES[0]: Can't open file $out_meta: $!\n";
	exit(-1);

}

if (not open HTML_OUTFILE, ">>$out_html"  ) {

	print "$ERROR_CODES[0]: Can't open file $out_html: $!\n";
	exit(-1);

}
if (not open LINK_OUTFILE, ">>$out_link"  ) {

	print "$ERROR_CODES[0]: Can't open file $out_link: $!\n";
	exit(-1);

}



$out_text_standard = $robots_base_name."text0";
$out_idx_standard = $robots_base_name."text.idx";
$out_geral_standard = $robots_base_name."geral0";
$out_geral_idx_standard = $robots_base_name."geral.idx";
$out_id_standard = $robots_base_name."id";
$out_prio_standard = $robots_base_name."prio";
$out_date_standard = $robots_base_name."date";
$out_meta_standard = $robots_base_name."meta0";
$out_meta_idx_standard = $robots_base_name."meta.idx";
$out_html_standard = $robots_base_name."html0";
$out_html_idx_standard = $robots_base_name."html.idx";
$out_link_standard = $robots_base_name."linkcontent0";
$out_link_idx_standard = $robots_base_name."linkcontent.idx";



for ($count_dir = 3+$VERBOSE_MODE; $count_dir <= $#ARGV; $count_dir ++)
  {
    if (not chdir ($ARGV[$count_dir])) {
	print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
	exit(-1);	
    } 
    $file_size = -s $out_text_standard;
    $file_geral_size = -s $out_geral_standard;
    $file_id_size = -s $out_id_standard;
    $file_prio_size = -s $out_prio_standard;
    $file_date_size = -s $out_date_standard;
    $file_meta_size = -s $out_meta_standard;
    $file_html_size = -s $out_html_standard;
    $file_link_size = -s $out_link_standard;

    $file_size_idx = -s $out_idx_standard;
    $file_geral_size_idx = -s $out_geral_idx_standard;
    $file_html_size_idx = -s $out_html_idx_standard;
    $file_link_size_idx = -s $out_link_idx_standard;

    $elementos = &Menor_Arquivos;

    if ($elementos){
      
      
      if ($offset_text + $file_size > $size_limit)
        {

  	  if ($VERBOSE_MODE) {

		print "Changing Text File\n"; 

	  } 
	  		
          $count_text ++;
          $offset_text = 0;
          if (not chdir ($home)) {
		print "$ERROR_CODES[1]: Can't access $home\n";
		exit(-1);
	  }
          close (TEXTFILE);
          $out_text = $base_name."text".$count_text;

          if (not open TEXTFILE, ">$out_text") {

		print "$ERROR_CODES[1]: Can't open file $out_text: $!\n";
		exit(-1);

	  }

	  if (not chdir ($ARGV[$count_dir])) {
	
		 print "$ERROR_CODES[1]: Can't cd to $ARGV[$count_dir]\n";
		 exit(-1);

	  } 


        }
      
      if ($offset_geral + $file_geral_size > $size_limit)
        {
          $count_geral ++;
          $offset_geral = 0;
          if (not chdir ($home)) {

		print "$ERROR_CODES[1]: Can't cd to $ARGV[$count_dir]\n";
		exit(-1);

	  }
          close (GERAL_OUTFILE);
          $out_geral = $base_name."geral".$count_geral;
          if (not open GERAL_OUTFILE, ">$out_geral") {

		print "$ERROR_CODES[0]: Can't open file $out_geral: $!\n";
		exit(-1);

	  }

	  if (not chdir ($ARGV[$count_dir])) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  }
 
        }
      
     if ($offset_meta + $file_meta_size > $size_limit)
        {
          $count_meta ++;
          $offset_meta = 0;
    
	  if (not chdir ($home)) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  }
   
          close (META_OUTFILE);
          $out_meta = $base_name."meta".$count_meta;
          if (not open META_OUTFILE, ">$out_meta") {

		print "$ERROR_CODES[0]: Can't open file $out_meta: $!\n";
		exit(-1);

	  }	
          if (not chdir ($ARGV[$count_dir])) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  } 
        }  
      
     if ($offset_html + $file_html_size > $size_limit)
        {
          $count_html ++;
          $offset_html = 0;
    
	  if (not chdir ($home)) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  }
   
          close (HTML_OUTFILE);
          $out_html = $base_name."html".$count_html;
          if (not open HTML_OUTFILE, ">$out_html") {

		print "$ERROR_CODES[0]: Can't open file $out_html: $!\n";
		exit(-1);

	  }	
          if (not chdir ($ARGV[$count_dir])) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  } 
        }  
      if ($offset_link + $file_link_size > $size_limit)
        {
          $count_link ++;
          $offset_link = 0;
    
	  if (not chdir ($home)) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  }
   
          close (LINK_OUTFILE);
          $out_link = $base_name."linkcontent".$count_link;
          if (not open LINK_OUTFILE, ">$out_link") {

		print "$ERROR_CODES[0]: Can't open file $out_link: $!\n";
		exit(-1);

	  }	
          if (not chdir ($ARGV[$count_dir])) {

		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	  } 
        }  
 
      if (not open TEXTFILE_STD, "$out_text_standard") {
 
		print "$ERROR_CODES[0]: Can't open file $out_text_standard: $!\n";
		exit(-1);

      }
      
      if (not open IDXFILE_STD, "$out_idx_standard") {
	
		print "$ERROR_CODES[0]: Can't open file $out_idx_standard: $!\n";
		exit(-1);

      }
      
      if (not open GERAL_OUTFILE_STD, "$out_geral_standard") {
 
		print "$ERROR_CODES[0]: Can't open file $out_geral_standard: $!\n";
		exit(-1);

      }

      if (not open GERAL_IDXFILE_STD, "$out_geral_idx_standard") {
 
		print "$ERROR_CODES[0]: Can't open file $out_geral_idx_standard: $!\n";
		exit(-1);

      }
      
      if (not open ID_OUTFILE_STD, "$out_id_standard") { 
 
		print "$ERROR_CODES[0]: Can't open file $out_id_standard: $!\n";
		exit(-1);
      
      }
      if (not open PRIO_OUTFILE_STD, "$out_prio_standard") { 
 
		print "$ERROR_CODES[0]: Can't open file $out_prio_standard: $!\n";
		exit(-1);
      
      }
      if (not open DATE_OUTFILE_STD, "$out_date_standard") { 
 
		print "$ERROR_CODES[0]: Can't open file $out_date_standard: $!\n";
		exit(-1);
      
      }


      $nometa = 0;
      open META_OUTFILE_STD, "$out_meta_standard" or $nometa = 1;
      open META_IDXFILE_STD, "$out_meta_idx_standard" or $nometa = 1;
      if ((-s $out_meta_standard) == 0) {
        $nometa = 1;
      }
 
      $nohtml = 0;
      open HTML_OUTFILE_STD, "$out_html_standard" or $nohtml = 1;
      open HTML_IDXFILE_STD, "$out_html_idx_standard" or $nohtml = 1;
      if ((-s $out_html_standard) == 0) {
        $nohtml = 1;
      } 
      
      $nolink = 0;
      open LINK_OUTFILE_STD, "$out_link_standard" or $nolink = 1;
      open LINK_IDXFILE_STD, "$out_link_idx_standard" or $nolink = 1;
      if ((-s $out_link_standard) == 0) {
        $nolink = 1;
      }            
   
      #---------------------- Arquivo de Texto
      read IDXFILE_STD, $file_count, 2;
      $file_count = unpack ("S", $file_count);
      
      read IDXFILE_STD, $file_offset, 4;
      $file_offset = unpack ("L", $file_offset);
      
      #----------------------- Arquivo de Gerals
      read GERAL_IDXFILE_STD, $file_geral_count, 2;
      $file_geral_count = unpack ("S", $file_geral_count);
      
      read GERAL_IDXFILE_STD, $file_geral_offset, 4;
      $file_geral_offset = unpack ("L", $file_geral_offset);
      
      if ($nometa == 0) {

        #---------------------- Arquivo de METAGS

	read META_IDXFILE_STD, $file_meta_count, 2;
	$file_meta_count = unpack ("S", $file_meta_count);
      
	read META_IDXFILE_STD, $file_meta_offset, 4;
	$file_meta_offset = unpack ("L", $file_meta_offset);

      }
      if ($nohtml == 0) {

        #---------------------- Arquivo de METAGS

	read HTML_IDXFILE_STD, $file_html_count, 2;
	$file_html_count = unpack ("S", $file_html_count);
      
	read HTML_IDXFILE_STD, $file_html_offset, 4;
	$file_html_offset = unpack ("L", $file_html_offset);

      }

      if ($nolink == 0) {

        #---------------------- Arquivo de LINKCONTENT

	read LINK_IDXFILE_STD, $file_link_count, 2;
	$file_link_count = unpack ("S", $file_link_count);
      
	read LINK_IDXFILE_STD, $file_link_offset, 4;
	$file_link_offset = unpack ("L", $file_link_offset);

      }


      $numero_lidos = 1;         
      if ($VERBOSE_MODE) {

	print "Current Directory". cwd(). "\n";

      } 
      $negativelength = 0;
      while (($numero_lidos < $elementos) && (!$negativelength))
        {    
          
          # ---------------- read offset texto
          read IDXFILE_STD, $file_count_prox, 2;
          $file_count_prox = unpack ("S", $file_count_prox);
          
          read IDXFILE_STD, $file_offset_prox, 4;
          $file_offset_prox = unpack ("L", $file_offset_prox);
          
          
          # ---------------- read offset geral
          read GERAL_IDXFILE_STD, $file_geral_count_prox, 2;
          $file_geral_count_prox = unpack ("S", $file_geral_count_prox);
          
          read GERAL_IDXFILE_STD, $file_geral_offset_prox, 4;
          $file_geral_offset_prox = unpack ("L", $file_geral_offset_prox);
          
	  if ($nometa == 0) {

	    #---------------------- Arquivo de METAGS

	    read META_IDXFILE_STD, $file_meta_count_prox, 2;
	    $file_meta_count_prox = unpack ("S", $file_meta_count_prox);
      
	    read META_IDXFILE_STD, $file_meta_offset_prox, 4;
	    $file_meta_offset_prox = unpack ("L", $file_meta_offset_prox);

	  }         

	  if ($nohtml == 0) {

	    #---------------------- Arquivo de HTML

	    read HTML_IDXFILE_STD, $file_html_count_prox, 2;
	    $file_html_count_prox = unpack ("S", $file_html_count_prox);
      
	    read HTML_IDXFILE_STD, $file_html_offset_prox, 4;
	    $file_html_offset_prox = unpack ("L", $file_html_offset_prox);

	  }         

	  if ($nolink == 0) {

	    #---------------------- Arquivo de LINK CONTENT

	    read LINK_IDXFILE_STD, $file_link_count_prox, 2;
	    $file_link_count_prox = unpack ("S", $file_link_count_prox);
      
	    read LINK_IDXFILE_STD, $file_link_offset_prox, 4;
	    $file_link_offset_prox = unpack ("L", $file_link_offset_prox);

	  }         


	  # ---------------- test offsets
          if ($file_count_prox != $file_count){
	    $file_offset_prox = $file_size;
	  }else{
	    if (($file_offset_prox - $file_offset) <= 0){
	      $negativelength = 1;
	    }
          }
	  
	  if ($file_geral_count_prox != $file_geral_count){
	    $file_geral_offset_prox = $file_geral_size;
	  }else{
	    if (($file_geral_offset_prox - $file_geral_offset) <= 0){
	      $negativelength = 1;
	    }
	  }
	  
	  if ($nometa == 0) {

	  	if ($file_meta_count_prox != $file_meta_count){
		    	$file_meta_offset_prox = $file_meta_size;
	  	}else{

		    	if (($file_meta_offset_prox - $file_meta_offset) <= 0){
			      	$negativelength = 1;
		    	}	
		 }
	  }
	  if ($nohtml == 0) {

	  	if ($file_html_count_prox != $file_html_count){
		    	$file_html_offset_prox = $file_html_size;
	  	}else{

		    	if (($file_html_offset_prox - $file_html_offset) <= 0){
			      	$negativelength = 1;
		    	}	
		 }
	  }

	  if ($nolink == 0) {

	  	if ($file_link_count_prox != $file_link_count){
		    	$file_link_offset_prox = $file_link_size;
	  	}else{

		    	if (($file_link_offset_prox - $file_link_offset) <= 0){
			      	$negativelength = 1;
		    	}	
		 }
	  }



          if (!$negativelength){
            
            #--------------------- Read the contents
            read TEXTFILE_STD, $text, ($file_offset_prox - $file_offset);
            
            read GERAL_OUTFILE_STD, $geral, ($file_geral_offset_prox - $file_geral_offset);
            
            
            read ID_OUTFILE_STD, $id, 1;

            read PRIO_OUTFILE_STD, $prio, 1;

            read DATE_OUTFILE_STD, $date, 4;

	    if ($nometa==0) {

	       read META_OUTFILE_STD, $meta, ($file_meta_offset_prox - $file_meta_offset);

	    }
       	    if ($nohtml==0) {

	       read HTML_OUTFILE_STD, $html, ($file_html_offset_prox - $file_html_offset);

	    }
       	    if ($nolink==0) {

	       read LINK_OUTFILE_STD, $link, ($file_link_offset_prox - $file_link_offset);

	    }
     
	    
	    if ($offset_text + ($file_offset_prox - $file_offset) >= $size_limit){
	      $count_text++;
	      $out_text = $home.$base_name."text".$count_text;
	      close TEXTFILE;
	      if (not open TEXTFILE, "+>>$out_text") { 

			print  "$ERROR_CODES[0]: Can't open file $out_text: $!\n";
			exit(-1);

	      }
	      $offset_text = 0;
	      if ($VERBOSE_MODE) {

		      print "Changing output text file to $out_text\n";	

      	      } 
 

	    }		 
            #--------------------- Atualiza o arquivo de textos. 
            syswrite IDXFILE, pack ("S", $count_text), 2;
            
            syswrite IDXFILE, pack ("L", $offset_text), 4;
            
            syswrite TEXTFILE, $text, ($file_offset_prox - $file_offset);
            
            $offset_text = $offset_text + ($file_offset_prox - $file_offset);
            
	    if ($file_count_prox != $file_count){
	      $file_count = $file_count_prox;
	      $file_offset = 0;
	      close(TEXTFILE_STD);
	      $out_text_standard = $robots_base_name."text".$file_count;
	      $file_size = -s $out_text_standard;
	      if (not open TEXTFILE_STD, "$out_text_standard"){
			print "$ERROR_CODES[0]: Can't open file $out_text_standard: $!\n";
			exit(-1);
	      }
	      if ($VERBOSE_MODE) {

	           print " Changing output text file to : $out_text_standard\n";

	      }
	    }else{
	      $file_count = $file_count_prox;
	      $file_offset = $file_offset_prox; 
	    }

            #--------------------- Atualiza o arquivo de Gerals. 
            syswrite GERAL_IDXFILE, pack ("S", $count_geral), 2;
            
            syswrite GERAL_IDXFILE, pack ("L", $offset_geral), 4;
            
            syswrite GERAL_OUTFILE, $geral, ($file_geral_offset_prox - $file_geral_offset);
            
            $offset_geral = $offset_geral + ($file_geral_offset_prox - $file_geral_offset);
            
	    if ($file_geral_count_prox != $file_geral_count){
	      $file_geral_count = $file_geral_count_prox;
	      $file_geral_offset = 0;
	      close(GERAL_OUTFILE_STD);
	      $out_geral_standard = $robots_base_name."geral".$file_geral_count;
	      $file_geral_size = -s $out_geral_standard;
	      if (not open GERAL_OUTFILE_STD, "$out_geral_standard") { 
 
			print "$ERROR_CODES[0]: Can't open file $out_geral_standard: $!\n"; 
			exit(-1);

	      }
	      if ($VERBOSE_MODE) {

	           print " Changing output geral file to : $out_geral_standard\n";

	      }
	

	    }else{
	      $file_geral_count = $file_geral_count_prox;
	      $file_geral_offset = $file_geral_offset_prox; 
	    }
	    
	    #-------------------- Atualiza o arquivo de IDs
            syswrite ID_OUTFILE, $id, 1; 

            #-------------------- Atualiza o arquivo de PRIO
            syswrite PRIO_OUTFILE, $prio, 1; 

            #-------------------- Atualiza o arquivo de DATE
            syswrite DATE_OUTFILE, $date, 4; 
         		   
            #-------------------- Atualiza o arquivo de metatags. 
            syswrite META_IDXFILE, pack ("S", $count_meta), 2;
            
            syswrite META_IDXFILE, pack ("L", $offset_meta), 4;
           
	    if ($nometa == 0) {

	      syswrite META_OUTFILE, $meta, ($file_meta_offset_prox - $file_meta_offset);
	      $offset_meta = $offset_meta + ($file_meta_offset_prox - $file_meta_offset);

	    } else {

		syswrite META_OUTFILE,pack ("x"),1;
 		$offset_meta += 1;

	    }
            
	    if ($file_meta_count_prox != $file_meta_count){
	      $file_meta_count = $file_meta_count_prox;
	      $file_meta_offset = 0;
	      close(META_OUTFILE_STD);
	      $out_meta_standard = $robots_base_name."meta".$file_meta_count;
	      $file_meta_size = -s $out_meta_standard;
	      if (not open META_OUTFILE_STD, "$out_meta_standard") {

			print "$ERROR_CODES[0]: Can't open file $out_meta_standard: $!\n";
			exit(-1);

		}
	      if ($VERBOSE_MODE) {

	           print " Changing output geral file to : $out_meta_standard\n";

	      }


	    }else{
	      $file_meta_count = $file_meta_count_prox;
	      $file_meta_offset = $file_meta_offset_prox; 
	    }

            #-------------------- Atualiza o arquivo de html. 
            syswrite HTML_IDXFILE, pack ("S", $count_html), 2;
            
            syswrite HTML_IDXFILE, pack ("L", $offset_html), 4;
           
	    if ($nohtml == 0) {

	      syswrite HTML_OUTFILE, $html, ($file_html_offset_prox - $file_html_offset);
              $offset_html = $offset_html + ($file_html_offset_prox - $file_html_offset);

	    } else {

		syswrite HTML_OUTFILE,pack ("x"),1;
 		$offset_html += 1;

	    }

            
	    
	    if ($file_html_count_prox != $file_html_count){
	      $file_html_count = $file_html_count_prox;
	      $file_html_offset = 0;
	      close(HTML_OUTFILE_STD);
	      $out_html_standard = $robots_base_name."html".$file_html_count;
	      $file_html_size = -s $out_html_standard;
	      if (not open HTML_OUTFILE_STD, "$out_html_standard") {

			print "$ERROR_CODES[0]: Can't open file $out_html_standard: $!\n";
			exit(-1);

		}
	      if ($VERBOSE_MODE) {

	           print " Changing output geral file to : $out_html_standard\n";

	      }


	    }else{
	      $file_html_count = $file_html_count_prox;
	      $file_html_offset = $file_html_offset_prox; 
	    }

            #-------------------- Atualiza o arquivo de link content. 
            syswrite LINK_IDXFILE, pack ("S", $count_link), 2;
            
            syswrite LINK_IDXFILE, pack ("L", $offset_link), 4;
           
	    if ($nolink == 0) {

	      syswrite LINK_OUTFILE, $link, ($file_link_offset_prox - $file_link_offset);
              $offset_link = $offset_link + ($file_link_offset_prox - $file_link_offset);

	    } else {

		syswrite LINK_OUTFILE,pack ("x"),1;
 		$offset_link += 1;

	    }

            
   	    
	    if ($file_link_count_prox != $file_link_count){
	      $file_link_count = $file_link_count_prox;
	      $file_link_offset = 0;
	      close(LINK_OUTFILE_STD);
	      $out_link_standard = $robots_base_name."linkcontent".$file_link_count;
	      $file_link_size = -s $out_link_standard;
	      if (not open LINK_OUTFILE_STD, "$out_link_standard") {

			print "$ERROR_CODES[0]: Can't open file $out_link_standard: $!\n";
			exit(-1);

		}
	      if ($VERBOSE_MODE) {

	           print " Changing output geral file to : $out_link_standard\n";

	      }


	    }else{
	      $file_link_count = $file_link_count_prox;
	      $file_link_offset = $file_link_offset_prox; 
	    }




	    
	    $numero_lidos ++;
          }
        }
      
      if ($negativelength){
        close (TEXTFILE_STD);
        close (IDXFILE_STD);
        
        close (GERAL_OUTFILE_STD);
        close (GERAL_IDXFILE_STD);
        
	close (ID_OUTFILE_STD);

       	close (PRIO_OUTFILE_STD);

	close (DATE_OUTFILE_STD);

        close (HTML_OUTFILE_STD);
        close (HTML_IDXFILE_STD);

	close (META_OUTFILE_STD);
        close (META_IDXFILE_STD);
 
        close (LINK_OUTFILE_STD);
        close (LINK_IDXFILE_STD);

       
	if (not chdir ($home)) {
		print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);
        }
        next;
      }
      
      if (($file_size_idx / 6) == $elementos)
        { 
          
          #------------------- Atualiza o arquivo de textos. 
          $file_offset_prox = $file_size;
        }
      else
        {
          #------------------- Atualiza o arquivo de textos.
          read IDXFILE_STD, $file_count_prox, 2;
          $file_count_prox = unpack ("S", $file_count_prox);
          
          read IDXFILE_STD, $file_offset_prox, 4;
          $file_offset_prox = unpack ("L", $file_offset_prox);
          
        }
      
      
      
      if (($file_geral_size_idx / 6) == $elementos)
        { 
          #------------------- Atualiza o arquivo de Gerals. 
          $file_geral_offset_prox = $file_geral_size;
        }
      else
        {
          #------------------- Atualiza o arquivo de Gerals.
          read GERAL_IDXFILE_STD, $file_geral_count_prox, 2;
          $file_geral_count_prox = unpack ("S", $file_geral_count_prox);
          
          read GERAL_IDXFILE_STD, $file_geral_offset_prox, 4;
          $file_geral_offset_prox = unpack ("L", $file_geral_offset_prox);
        }
      
      
      if (($file_meta_size_idx / 6) == $elementos)
        {
          $file_meta_offset_prox  = $file_meta_size;
        }
      else
        {

	  if ($nometa == 0) {

	    read META_IDXFILE_STD, $file_meta_count_prox, 2;
	    $file_meta_count_prox = unpack ("S", $file_meta_count_prox);
          
	    read META_IDXFILE_STD, $file_meta_offset_prox, 4;
	    $file_meta_offset_prox = unpack ("L", $file_meta_offset_prox);

	  }
        }
      
       if (($file_html_size_idx / 6) == $elementos)
        {
          $file_html_offset_prox  = $file_html_size;
        }
      else
        {

	  if ($nohtml == 0) {

	    read HTML_IDXFILE_STD, $file_html_count_prox, 2;
	    $file_html_count_prox = unpack ("S", $file_html_count_prox);
          
	    read HTML_IDXFILE_STD, $file_html_offset_prox, 4;
	    $file_html_offset_prox = unpack ("L", $file_html_offset_prox);

	  }
        }
      
       if (($file_link_size_idx / 6) == $elementos)
        {
          $file_link_offset_prox  = $file_link_size;
        }
      else
        {

	  if ($nolink == 0) {

	    read LINK_IDXFILE_STD, $file_link_count_prox, 2;
	    $file_link_count_prox = unpack ("S", $file_link_count_prox);
          
	    read LINK_IDXFILE_STD, $file_link_offset_prox, 4;
	    $file_link_offset_prox = unpack ("L", $file_link_offset_prox);

	  }
        }
 
      # ---------------- test offsets
      
      if (($file_offset_prox - $file_offset) <= 0){
        $negativelength = 1;
      }
      
      
      if (($file_geral_offset_prox - $file_geral_offset) <= 0){
        $negativelength = 1;
      }
      
      if (($file_meta_offset_prox - $file_meta_offset) <= 0){
        $negativelength = 1;
      }
      
      if ($negativelength){
        close (TEXTFILE_STD);
        close (IDXFILE_STD);
        
        close (GERAL_OUTFILE_STD);
        close (GERAL_IDXFILE_STD);
        
	close (ID_OUTFILE_STD);

	close (PRIO_OUTFILE_STD);

	close (DATE_OUTFILE_STD);

        close (ABS_OUTFILE_STD);
        close (ABS_IDXFILE_STD);

        close (META_OUTFILE_STD);
        close (META_IDXFILE_STD);
 
        close (LINK_OUTFILE_STD);
        close (LINK_IDXFILE_STD);
        
	if (not chdir ($home)) { 
		
		print "$ERROR_CODES[1]: Can't access to $ARGV[$count_dir]\n";
   		exit(-1);

	}

        next;
      }
      
      #--------------------- Read the contents
      read TEXTFILE_STD, $text, ($file_offset_prox - $file_offset);
      
      read GERAL_OUTFILE_STD, $geral, ($file_geral_offset_prox - $file_geral_offset);
      
      read ID_OUTFILE_STD, $id, 1;

      read PRIO_OUTFILE_STD, $prio, 1;

      read DATE_OUTFILE_STD, $date, 4;

      if ($nometa==0) {

	read META_OUTFILE_STD, $meta, ($file_meta_offset_prox - $file_meta_offset);

      }
      if ($nohtml==0) {

	read HTML_OUTFILE_STD, $html, ($file_html_offset_prox - $file_html_offset);

      }
      
      if ($nolink==0) {

	read LINK_OUTFILE_STD, $link, ($file_link_offset_prox - $file_link_offset);

      }
      
      #--------------------- Atualiza o arquivo de textos. 
      syswrite IDXFILE, pack ("S", $count_text), 2;
      
      syswrite IDXFILE, pack ("L", $offset_text), 4;
      
      syswrite TEXTFILE, $text, ($file_offset_prox - $file_offset);
      
      $offset_text = $offset_text + ($file_offset_prox - $file_offset);
      
      $file_count = $file_count_prox;
      $file_offset = $file_offset_prox; 
      
      #--------------------- Atualiza o arquivo de Gerals. 
      syswrite GERAL_IDXFILE, pack ("S", $count_geral), 2;
      
      syswrite GERAL_IDXFILE, pack ("L", $offset_geral), 4;
      
      syswrite GERAL_OUTFILE, $geral, ($file_geral_offset_prox - $file_geral_offset);
      
      $offset_geral = $offset_geral + ($file_geral_offset_prox - $file_geral_offset);
      
      $file_geral_count = $file_geral_count_prox;
      $file_geral_offset = $file_geral_offset_prox; 
      
            
      #-------------------- Atualiza o arquivo de baseids.
      syswrite ID_OUTFILE, $id, 1; 
      
      #-------------------- Atualiza o arquivo de prioridade.
      syswrite PRIO_OUTFILE, $prio, 1; 

      #-------------------- Atualiza o arquivo de baseids.
      syswrite DATE_OUTFILE, $date, 4; 
     
      #-------------------- Atualiza o arquivo de metatags. 
      syswrite META_IDXFILE, pack ("S", $count_meta), 2;

      syswrite META_IDXFILE, pack ("L", $offset_meta), 4;
      
      if ($nometa == 0) {

	syswrite META_OUTFILE, $meta, ($file_meta_offset_prox - $file_meta_offset);
        $offset_meta = $offset_meta + ($file_meta_offset_prox - $file_meta_offset);

      } else {

	syswrite META_OUTFILE,pack ("x"),1;
	$offset_meta += 1;

      }
 
      
      
      $file_meta_count = $file_meta_count_prox;
      $file_meta_offset = $file_meta_offset_prox; 
      
      #-------------------- Atualiza o arquivo de html. 
      syswrite HTML_IDXFILE, pack ("S", $count_html), 2;

      syswrite HTML_IDXFILE, pack ("L", $offset_html), 4;
      
      if ($nohtml == 0) {

	syswrite HTML_OUTFILE, $html, ($file_html_offset_prox - $file_html_offset);
        $offset_html = $offset_html + ($file_html_offset_prox - $file_html_offset);

      } else {

	syswrite HTML_OUTFILE,pack ("x"),1;
 	$offset_html += 1;

      }

      
          
      $file_html_count = $file_html_count_prox;
      $file_html_offset = $file_html_offset_prox; 
 
      #-------------------- Atualiza o arquivo de link. 
      syswrite LINK_IDXFILE, pack ("S", $count_link), 2;

      syswrite LINK_IDXFILE, pack ("L", $offset_link), 4;
      
      if ($nolink == 0) {

	syswrite LINK_OUTFILE, $link, ($file_link_offset_prox - $file_link_offset);
        $offset_link = $offset_link + ($file_link_offset_prox - $file_link_offset);

      } else {

	syswrite LINK_OUTFILE,pack ("x"),1;
 	$offset_link += 1;

      }

      
      
      $file_link_count = $file_link_count_prox;
      $file_link_offset = $file_link_offset_prox; 
      
      close (TEXTFILE_STD);
      close (IDXFILE_STD);
      
      close (GERAL_OUTFILE_STD);
      close (GERAL_IDXFILE_STD);
      
      close (PRIO_OUTFILE_STD);

      close (DATE_OUTFILE_STD);

      close (ID_OUTFILE_STD);
   
      close (HTML_OUTFILE_STD);
      close (HTML_IDXFILE_STD);

      close (META_OUTFILE_STD);
      close (META_IDXFILE_STD);
 
      close (LINK_OUTFILE_STD);
      close (LINK_IDXFILE_STD);
      
      if (not chdir ($home)) {

		 print "$ERROR_CODES[1]: Can't access $ARGV[$count_dir]\n";
		exit(-1);

	}	
    }
  }
close (TEXTFILE);
close (IDXFILE);

close (GERAL_OUTFILE);
close (GERAL_IDXFILE);

close (ID_OUTFILE);

close (DATE_OUTFILE);

close (PRIO_OUTFILE);

close (HTML_OUTFILE);
close (HTML_IDXFILE);

close (META_OUTFILE);
close (META_IDXFILE);

close (LINK_OUTFILE);
close (LINK_IDXFILE);


print "00000: Program executed with success\n";

exit(0);


#-------------------------------- Menor Arquivos -----------------------------
sub Menor_Arquivos
  {
    my ($elementos);
    
    $elementos = ($file_size_idx / 6); 
    
    if (($file_geral_size_idx / 6) < $elementos)
      {
        $elementos = ($file_geral_size_idx / 6);
      } 
    
    if (($file_id_size) < $elementos)
      {
        $elementos = $file_id_size;
      } 
    
    
    return ($elementos);
  } 

#------------------------ Init Count Offset ---------------------
sub Init_Count_Offset
  {
    my ($fileHandle, $nameFile, $count, $offset) = @_;
    my ($file_count, $file_offset, $name_text, $name_idx);
    
    $name_text = $base_name.$nameFile;
    $name_idx = $base_name . $nameFile . ".idx";
    
    if ((-s $name_idx) == 0)
      {
        $$count = 0;
        $$offset = 0; 
      }
    else
      {
        seek ($$fileHandle, ((-s ($name_idx)) - 6), 0);
        
        read $$fileHandle, $file_count, 2;
        $file_count = unpack ("S", $file_count);
        
        read $$fileHandle, $file_offset, 4;
        $file_offset = unpack ("L", $file_offset);
        
        
        $$count = $file_count;
	if ($VERBOSE_MODE) {

	     print " Counter : $$count\n$file_count \n";

	}



        $name_text = $name_text.$$count;
        
        $$offset = (-s ($name_text));

      }
  }

#------------------------ Init Id Size ---------------------
sub Init_Id_Size
  {
    my ($fileHandle, $nameFile) = @_;
    
    if ((-s $base_name . $nameFile) != 0)
      {
        seek ($$fileHandle, (-s ($base_name . $nameFile)), 0);
      }
  }



