#!/usr/bin/perl
#use strict;
use Cwd;

my ($base_name, $home,$robots_base_name);
my ($out_text, $out_idx, $out_geral, $out_geral_idx, $out_size, $out_id, $out_abs, $out_abs_idx, $out_title, $out_title_idx, $out_type, $out_type_idx);
my ($count_text, $offset_text, $count_geral, $offset_geral, $count_abs, $offset_abs, $count_title, $offset_title, $count_type, $offset_type, $count_meta, $offset_meta);
my ($out_text_standard, $out_idx_standard, $out_geral_standard, $out_geral_idx_standard, $out_size_standard, $out_id_standard, $out_abs_standard, $out_abs_idx_standard, $out_title_standard, $out_title_idx_standard, $out_type_standard, $out_type_idx_standard);
my ($file_size, $file_geral_size, $file_abs_size, $file_title_size, $file_type_size, $file_size_size, $file_id_size, $file_meta_size);
my ($file_size_idx, $file_geral_size_idx, $file_abs_size_idx, $file_title_size_idx, $file_type_size_idx, $file_meta_size_idx);

my ($size_limit);
my ($count_dir, $text);
my ($file_count, $file_count_prox, $file_offset, $file_offset_prox);
my ($file_geral_count, $file_geral_count_prox, $file_geral_offset, $file_geral_offset_prox); 
my ($file_abs_count, $file_abs_count_prox, $file_abs_offset, $file_abs_offset_prox);
my ($file_title_count, $file_title_count_prox, $file_title_offset, $file_title_offset_prox); 
my ($file_type_count, $file_type_count_prox, $file_type_offset, $file_type_offset_prox); 
my ($numero_lidos, $elementos);

if (@ARGV < 4){
  die "usage :\nagrupar_novo.pl <robots> <collection> <home> [dirs]\n";
}

$robots_base_name = $ARGV[0];
$base_name = $ARGV[1];
$home = $ARGV[2];

$size_limit = 2**31;
 
chdir ($home) or die "Can't cd to $home $!\n";

$out_idx = $base_name."text.idx";

$out_geral_idx = $base_name."geral.idx";

$out_id = $base_name."id";

$out_abs_idx = $base_name."abstract.idx";

$out_meta_idx = $base_name."meta.idx";


open IDXFILE, "+>>$out_idx" or die "Can't open file $out_idx: $!\n";

open GERAL_IDXFILE, "+>>$out_geral_idx" or die "Can't open file $out_geral_idx: $!\n";
open ID_OUTFILE, "+>>$out_id" or die "Can't open file $out_id: $!\n";

open ABS_IDXFILE, "+>>$out_abs_idx" or die "Can't open file $out_abs_idx: $!\n";

open META_IDXFILE, "+>>$out_meta_idx" or die "Can't open file $out_meta_idx: $!\n";

&Init_Count_Offset (\IDXFILE, "text", \$count_text, \$offset_text);
&Init_Count_Offset (\GERAL_IDXFILE, "geral", \$count_geral, \$offset_geral);
&Init_Count_Offset (\ABS_IDXFILE, "abstract", \$count_abs, \$offset_abs);
&Init_Count_Offset (\META_IDXFILE, "meta", \$count_meta, \$offset_meta);

&Init_Id_Size (\ID_OUTFILE, "id");

$out_text = $base_name."text".$count_text;
print $ENV{PWD};

$out_geral = $base_name."geral".$count_geral;

$out_abs = $base_name."abstract".$count_abs;

$out_meta = $base_name."meta".$count_meta;


open TEXTFILE, "+>>$out_text" or die "Can't open file $out_text: $!\n";
print "$out_text\n";

open GERAL_OUTFILE, ">>$out_geral" or die "Can't open file $out_geral: $!\n";
open ABS_OUTFILE, ">>$out_abs" or die "Can't open file $out_abs: $!\n";
open META_OUTFILE, ">>$out_meta" or die "Can't open file $out_meta: $!\n";



$out_text_standard = $robots_base_name."text0";
$out_idx_standard = $robots_base_name."text.idx";
$out_geral_standard = $robots_base_name."geral0";
$out_geral_idx_standard = $robots_base_name."geral.idx";
$out_id_standard = $robots_base_name."id";
$out_abs_standard = $robots_base_name."abstract0";
$out_abs_idx_standard = $robots_base_name."abstract.idx";
$out_meta_standard = $robots_base_name."meta0";
$out_meta_idx_standard = $robots_base_name."meta.idx";

for ($count_dir = 3; $count_dir <= $#ARGV; $count_dir ++)
  {
    chdir ($ARGV[$count_dir]) or die "Can't cd to $ARGV[$count_dir]\n";
    
    $file_size = -s $out_text_standard;
    $file_geral_size = -s $out_geral_standard;
    $file_id_size = -s $out_id_standard;
    $file_abs_size = -s $out_abs_standard; 
    $file_meta_size = -s $out_meta_standard;

    $file_size_idx = -s $out_idx_standard;
    $file_geral_size_idx = -s $out_geral_idx_standard;
    $file_abs_size_idx = -s $out_abs_idx_standard;
    $file_meta_size_idx = -s $out_meta_idx_standard;

    $elementos = &Menor_Arquivos;

    if ($elementos){
      
      
      if ($offset_text + $file_size > $size_limit)
        {
          print "Tentando Mudar de arquivo de texto\n";
          $count_text ++;
          $offset_text = 0;
          chdir ($home) or die "Can't cd to $home\n";
          close (TEXTFILE);
          $out_text = $base_name."text".$count_text;
          print "Mudando de arquivo de texto\n";
          open TEXTFILE, ">$out_text" or die "Can't open file $out_text: $!\n";
          chdir ($ARGV[$count_dir]) or die "Can't cd to $ARGV[$count_dir]\n"; 
        }
      
      if ($offset_geral + $file_geral_size > $size_limit)
        {
          $count_geral ++;
          $offset_geral = 0;
          chdir ($home) or die "Can't cd to $ARGV[$count_dir]\n";
          close (GERAL_OUTFILE);
          $out_geral = $base_name."geral".$count_geral;
          open GERAL_OUTFILE, ">$out_geral" or die "Can't open file $out_geral: $!\n";
          chdir ($ARGV[$count_dir]) or die "Can't cd to $ARGV[$count_dir]\n"; 
        }
      
      if ($offset_abs + $file_abs_size > $size_limit)
        {
          $count_abs ++;
          $offset_abs = 0;
          chdir ($home) or die "Can't cd to $ARGV[$count_dir]\n";
          close (ABS_OUTFILE);
          $out_abs = $base_name."abstract".$count_abs;
          open ABS_OUTFILE, ">$out_abs" or die "Can't open file $out_abs: $!\n";
          chdir ($ARGV[$count_dir]) or die "Can't cd to $ARGV[$count_dir]\n"; 
        }  

     if ($offset_meta + $file_meta_size > $size_limit)
        {
          $count_meta ++;
          $offset_meta = 0;
          chdir ($home) or die "Can't cd to $ARGV[$count_dir]\n";
          close (META_OUTFILE);
          $out_meta = $base_name."meta".$count_meta;
          open META_OUTFILE, ">$out_meta" or die "Can't open file $out_meta: $!\n";
          chdir ($ARGV[$count_dir]) or die "Can't cd to $ARGV[$count_dir]\n"; 
        }  
      
      open TEXTFILE_STD, "$out_text_standard" or die "$0 Can't open file $out_text_standard: $!\n";
      
      open IDXFILE_STD, "$out_idx_standard" or die "Can't open file $out_idx_standard: $!\n";
      
      open GERAL_OUTFILE_STD, "$out_geral_standard" or die "Can't open file $out_geral_standard: $!\n";
      open GERAL_IDXFILE_STD, "$out_geral_idx_standard" or die "Can't open file $out_geral_idx_standard: $!\n";
      
      open ID_OUTFILE_STD, "$out_id_standard" or die "Can't open file $out_id_standard: $!\n";
      
      open ABS_OUTFILE_STD, "$out_abs_standard" or die "Can't open file $out_abs_standard: $!\n";
      open ABS_IDXFILE_STD, "$out_abs_idx_standard" or die "Can't open file $out_abs_idx_standard: $!\n";

      $nometa = 0;
      open META_OUTFILE_STD, "$out_meta_standard" or $nometa = 1;
      open META_IDXFILE_STD, "$out_meta_idx_standard" or $nometa = 1;
            
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
      
      #---------------------- Arquivo de Resumos
      read ABS_IDXFILE_STD, $file_abs_count, 2;
      $file_abs_count = unpack ("S", $file_abs_count);
      
      read ABS_IDXFILE_STD, $file_abs_offset, 4;
      $file_abs_offset = unpack ("L", $file_abs_offset);
      
      
      if ($nometa == 0) {

        #---------------------- Arquivo de METAGS

	read META_IDXFILE_STD, $file_meta_count, 2;
	$file_meta_count = unpack ("S", $file_meta_count);
      
	read META_IDXFILE_STD, $file_meta_offset, 4;
	$file_meta_offset = unpack ("L", $file_meta_offset);

      }

      $numero_lidos = 1;         
      print  "Diretorio:", cwd(), "\n";
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
          
          # ---------------- read offset abstract
          read ABS_IDXFILE_STD, $file_abs_count_prox, 2;
          $file_abs_count_prox = unpack ("S", $file_abs_count_prox);
          
          read ABS_IDXFILE_STD, $file_abs_offset_prox, 4;
          $file_abs_offset_prox = unpack ("L", $file_abs_offset_prox);
          
	  if ($nometa == 0) {

	    #---------------------- Arquivo de METAGS

	    read META_IDXFILE_STD, $file_meta_count_prox, 2;
	    $file_meta_count_prox = unpack ("S", $file_meta_count_prox);
      
	    read META_IDXFILE_STD, $file_meta_offset_prox, 4;
	    $file_meta_offset_prox = unpack ("L", $file_meta_offset_prox);

	  }         
	  # ---------------- test offsets
          if ($file_count_prox != $file_count){
	    $file_offset_prox = $file_size;
	  }else{
	    if (($file_offset_prox - $file_offset) <= 0){
	      print "Negative length no texto 1\n";
	      $negativelength = 1;
	    }
          }
	  
	  if ($file_geral_count_prox != $file_geral_count){
	    $file_geral_offset_prox = $file_geral_size;
	  }else{
	    if (($file_geral_offset_prox - $file_geral_offset) <= 0){
	      print "Negative length na geral 1\n";
	      $negativelength = 1;
	    }
	  }
	  
	  if ($file_abs_count_prox != $file_abs_count){
	    $file_abs_offset_prox = $file_abs_size;
	  }else{
	    if (($file_abs_offset_prox - $file_abs_offset) <= 0){
	      print "Negative length no abstract 1\n";
	      $negativelength = 1;
	    }
	  }

	  if ($nometa == 0) {

	  	if ($file_meta_count_prox != $file_meta_count){
	    	$file_meta_offset_prox = $file_meta_size;
	  	}else{
	    	if (($file_meta_offset_prox - $file_meta_offset) <= 0){
	     	 print "Negative length no meta 1\n";
	      	$negativelength = 1;
	    	}	
	 		 }
		}
          if (!$negativelength){
            
            #--------------------- Read the contents
            read TEXTFILE_STD, $text, ($file_offset_prox - $file_offset);
            
            read GERAL_OUTFILE_STD, $geral, ($file_geral_offset_prox - $file_geral_offset);
            
            read ABS_OUTFILE_STD, $abs, ($file_abs_offset_prox - $file_abs_offset);
            
            read ID_OUTFILE_STD, $id, 1;

	    if ($nometa==0) {

	       read META_OUTFILE_STD, $meta, ($file_meta_offset_prox - $file_meta_offset);

	    }
            
	    
	    if ($offset_text + ($file_offset_prox - $file_offset) >= $size_limit){
	      $count_text++;
	      $out_text = $home.$base_name."text".$count_text;
	      close TEXTFILE;
	      open TEXTFILE, "+>>$out_text" or die "Can't open file $out_text: $!\n";
	      $offset_text = 0;
	      print "*** changing to $out_text\n";	
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
	      open TEXTFILE_STD, "$out_text_standard" or die "$0 Can't open file $out_text_standard: $!\n";
	      print "Mudando de arquivo : $out_text_standard\n";
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
	      $out_geral_standard = $robots_base_name."geral".$file_count;
	      $file_geral_size = -s $out_geral_standard;
	      open GERAL_OUTFILE_STD, "$out_geral_standard" or die "$0 Can't open file $out_geral_standard: $!\n"; 
	      print "Mudando de arquivo : $out_geral_standard\n";
	    }else{
	      $file_geral_count = $file_geral_count_prox;
	      $file_geral_offset = $file_geral_offset_prox; 
	    }
	    

            #-------------------- Atualiza o arquivo de resumos. 
            syswrite ABS_IDXFILE, pack ("S", $count_abs), 2;
            
            syswrite ABS_IDXFILE, pack ("L", $offset_abs), 4;
            
            syswrite ABS_OUTFILE, $abs, ($file_abs_offset_prox - $file_abs_offset);
            
            $offset_abs = $offset_abs + ($file_abs_offset_prox - $file_abs_offset);
	    
	    if ($file_abs_count_prox != $file_abs_count){
	      $file_abs_count = $file_abs_count_prox;
	      $file_abs_offset = 0;
	      close(ABS_OUTFILE_STD);
	      $out_abs_standard = $robots_base_name."abs".$file_count;
	      $file_abs_size = -s $out_abs_standard;
	      open ABS_OUTFILE_STD, "$out_abs_standard" or die "$0 Can't open file $out_abs_standard: $!\n";
	      print "Mudando de arquivo : $out_abs_standard\n";
	    }else{
	      $file_abs_count = $file_abs_count_prox;
	      $file_abs_offset = $file_abs_offset_prox; 
	    }

	    #-------------------- Atualiza o arquivo de IDs
            syswrite ID_OUTFILE, $id, 1; 
            
            #-------------------- Atualiza o arquivo de metatags. 
            syswrite META_IDXFILE, pack ("S", $count_meta), 2;
            
            syswrite META_IDXFILE, pack ("L", $offset_meta), 4;
           
	    if ($nometa == 0) {

	      syswrite META_OUTFILE, $meta, ($file_meta_offset_prox - $file_meta_offset);

	    }
            
            $offset_meta = $offset_meta + ($file_meta_offset_prox - $file_meta_offset);
	    
	    if ($file_meta_count_prox != $file_meta_count){
	      $file_meta_count = $file_meta_count_prox;
	      $file_meta_offset = 0;
	      close(META_OUTFILE_STD);
	      $out_meta_standard = $robots_base_name."meta".$file_count;
	      $file_meta_size = -s $out_meta_standard;
	      open META_OUTFILE_STD, "$out_meta_standard" or die "$0 Can't open file $out_meta_standard: $!\n";
	      print "Mudando de arquivo : $out_meta_standard\n";
	    }else{
	      $file_meta_count = $file_meta_count_prox;
	      $file_meta_offset = $file_meta_offset_prox; 
	    }

	    
	    $numero_lidos ++;
          }else{
            print "foi no meio\n";
          }
        }
      
      if ($negativelength){
        close (TEXTFILE_STD);
        close (IDXFILE_STD);
        
        close (GERAL_OUTFILE_STD);
        close (GERAL_IDXFILE_STD);
        
	close (ID_OUTFILE_STD);
        
        close (ABS_OUTFILE_STD);
        close (ABS_IDXFILE_STD);

	close (META_OUTFILE_STD);
        close (META_IDXFILE_STD);
        
	chdir ($home) or die "Can't cd to $ARGV[$count_dir]\n";
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
      
      
      if (($file_abs_size_idx / 6) == $elementos)
        {
          $file_abs_offset_prox  = $file_abs_size;
        }
      else
        {
          read ABS_IDXFILE_STD, $file_abs_count_prox, 2;
          $file_abs_count_prox = unpack ("S", $file_abs_count_prox);
          
          read ABS_IDXFILE_STD, $file_abs_offset_prox, 4;
          $file_abs_offset_prox = unpack ("L", $file_abs_offset_prox);
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
      
      

      # ---------------- test offsets
      
      if (($file_offset_prox - $file_offset) <= 0){
        print "Negative length no texto 2\n";
        $negativelength = 1;
      }
      
      
      if (($file_geral_offset_prox - $file_geral_offset) <= 0){
        print "Negative length na geral 2\n";
        $negativelength = 1;
      }
      
      if (($file_abs_offset_prox - $file_abs_offset) <= 0){
        print "Negative length no abstract 2\n";
        $negativelength = 1;
      }

      if (($file_meta_offset_prox - $file_meta_offset) <= 0){
        print "Negative length no abstract 2\n";
        $negativelength = 1;
      }
      
      if ($negativelength){
        close (TEXTFILE_STD);
        close (IDXFILE_STD);
        
        close (GERAL_OUTFILE_STD);
        close (GERAL_IDXFILE_STD);
        
	close (ID_OUTFILE_STD);
        
        close (ABS_OUTFILE_STD);
        close (ABS_IDXFILE_STD);

        close (META_OUTFILE_STD);
        close (META_IDXFILE_STD);
        
	chdir ($home) or die "Can't cd to $ARGV[$count_dir]\n";
        next;
      }
      
      #--------------------- Read the contents
      read TEXTFILE_STD, $text, ($file_offset_prox - $file_offset);
      
      read GERAL_OUTFILE_STD, $geral, ($file_geral_offset_prox - $file_geral_offset);
      
      read ABS_OUTFILE_STD, $abs, ($file_abs_offset_prox - $file_abs_offset);
      
      read ID_OUTFILE_STD, $id, 1;

      if ($nometa==0) {

	read META_OUTFILE_STD, $meta, ($file_meta_offset_prox - $file_meta_offset);

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
      
      
      #-------------------- Atualiza o arquivo de resumos. 
      syswrite ABS_IDXFILE, pack ("S", $count_abs), 2;
      
      syswrite ABS_IDXFILE, pack ("L", $offset_abs), 4;
      
      syswrite ABS_OUTFILE, $abs, ($file_abs_offset_prox - $file_abs_offset);
      
      $offset_abs = $offset_abs + ($file_abs_offset_prox - $file_abs_offset);
      
      $file_abs_count = $file_abs_count_prox;
      $file_abs_offset = $file_abs_offset_prox; 

      #-------------------- Atualiza o arquivo de metatags. 
      syswrite META_IDXFILE, pack ("S", $count_meta), 2;

      syswrite META_IDXFILE, pack ("L", $offset_meta), 4;
      
      if ($nometa == 0) {

	syswrite META_OUTFILE, $abs, ($file_meta_offset_prox - $file_meta_offset);

      }
      
      $offset_meta = $offset_meta + ($file_meta_offset_prox - $file_meta_offset);
      
      $file_meta_count = $file_meta_count_prox;
      $file_meta_offset = $file_meta_offset_prox; 
      
      
      close (TEXTFILE_STD);
      close (IDXFILE_STD);
      
      close (GERAL_OUTFILE_STD);
      close (GERAL_IDXFILE_STD);
      
      close (ID_OUTFILE_STD);
      
      close (ABS_OUTFILE_STD);
      close (ABS_IDXFILE_STD);

      close (META_OUTFILE_STD);
      close (META_IDXFILE_STD);
      
      chdir ($home) or die "Can't cd to $ARGV[$count_dir]\n";
    }
  }
close (TEXTFILE);
close (IDXFILE);

close (GERAL_OUTFILE);
close (GERAL_IDXFILE);

close (ID_OUTFILE);

close (ABS_OUTFILE);
close (ABS_IDXFILE);

close (META_OUTFILE);
close (META_IDXFILE);


#-------------------------------- Menor Arquivos -----------------------------
sub Menor_Arquivos
  {
    my ($elementos);
    
    $elementos = ($file_size_idx / 6); 
    
    if (($file_geral_size_idx / 6) < $elementos)
      {
        $elementos = ($file_geral_size_idx / 6);
      } 
    
    if (($file_abs_size_idx / 6) < $elementos)
      {
        $elementos = ($file_abs_size_idx / 6);
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
    print "$name_text\n$name_idx\n";
    
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
        print "Contador:","$$count\n$file_count\n";
        $name_text = $name_text.$$count;
        print "$name_text\n";
        
        $$offset = (-s ($name_text));
        print "$$offset\n";
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



