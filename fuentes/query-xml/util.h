#if !defined(_UTIL_H)
#define _UTIL_H

#include <string.h>
#include <stdio.h>

//using namespace std;

#define NOEXT 0
#define HTML 1
#define PS 2
#define PDF 3
#define XLS 4
#define PPT 5
#define DOC 6

#define ENG_CRITERIA 0.02
#define LARGO_PAL 5

#define SHORT_URL_SIZE 40
#define MAX_TAG_SIZE 1000

#define NSTOP_WORDS 426

const char *START_HIGHLIGHT="<span class=\"highlight\">";
const char *END_HIGHLIGHT="</span>";

//string STOP_WORDS[]={"a"
char *STOP_WORDS[]={(char *)"a"
,(char *)"about"
,(char *)"above"
,(char *)"across"
,(char *)"after"
,(char *)"again"
,(char *)"against"
,(char *)"all"
,(char *)"almost"
,(char *)"alone"
,(char *)"along"
,(char *)"already"
,(char *)"also"
,(char *)"although"
,(char *)"always"
,(char *)"among"
,(char *)"an"
,(char *)"and"
,(char *)"another"
,(char *)"any"
,(char *)"anybody"
,(char *)"anyone"
,(char *)"anything"
,(char *)"anywhere"
,(char *)"are"
,(char *)"area"
,(char *)"areas"
,(char *)"around"
,(char *)"as"
,(char *)"ask"
,(char *)"asked"
,(char *)"asking"
,(char *)"asks"
,(char *)"at"
,(char *)"away"
,(char *)"b"
,(char *)"back"
,(char *)"backed"
,(char *)"backing"
,(char *)"backs"
,(char *)"be"
,(char *)"became"
,(char *)"because"
,(char *)"become"
,(char *)"becomes"
,(char *)"been"
,(char *)"before"
,(char *)"began"
,(char *)"behind"
,(char *)"being"
,(char *)"beings"
,(char *)"best"
,(char *)"better"
,(char *)"between"
,(char *)"big"
,(char *)"both"
,(char *)"but"
,(char *)"by"
,(char *)"c"
,(char *)"came"
,(char *)"can"
,(char *)"cannot"
,(char *)"case"
,(char *)"cases"
,(char *)"certain"
,(char *)"certainly"
,(char *)"clear"
,(char *)"clearly"
,(char *)"come"
,(char *)"could"
,(char *)"d"
,(char *)"did"
,(char *)"differ"
,(char *)"different"
,(char *)"differently"
,(char *)"do"
,(char *)"does"
,(char *)"done"
,(char *)"down"
,(char *)"down"
,(char *)"downed"
,(char *)"downing"
,(char *)"downs"
,(char *)"during"
,(char *)"e"
,(char *)"each"
,(char *)"early"
,(char *)"either"
,(char *)"end"
,(char *)"ended"
,(char *)"ending"
,(char *)"ends"
,(char *)"enough"
,(char *)"even"
,(char *)"evenly"
,(char *)"ever"
,(char *)"every"
,(char *)"everybody"
,(char *)"everyone"
,(char *)"everything"
,(char *)"everywhere"
,(char *)"f"
,(char *)"face"
,(char *)"faces"
,(char *)"fact"
,(char *)"facts"
,(char *)"far"
,(char *)"felt"
,(char *)"few"
,(char *)"find"
,(char *)"finds"
,(char *)"first"
,(char *)"for"
,(char *)"four"
,(char *)"from"
,(char *)"full"
,(char *)"fully"
,(char *)"further"
,(char *)"furthered"
,(char *)"furthering"
,(char *)"furthers"
,(char *)"g"
,(char *)"gave"
,(char *)"general"
,(char *)"generally"
,(char *)"get"
,(char *)"gets"
,(char *)"give"
,(char *)"given"
,(char *)"gives"
,(char *)"go"
,(char *)"going"
,(char *)"good"
,(char *)"goods"
,(char *)"got"
,(char *)"great"
,(char *)"greater"
,(char *)"greatest"
,(char *)"group"
,(char *)"grouped"
,(char *)"grouping"
,(char *)"groups"
,(char *)"h"
,(char *)"had"
,(char *)"has"
,(char *)"have"
,(char *)"having"
,(char *)"he"
,(char *)"her"
,(char *)"here"
,(char *)"herself"
,(char *)"high"
,(char *)"high"
,(char *)"high"
,(char *)"higher"
,(char *)"highest"
,(char *)"him"
,(char *)"himself"
,(char *)"his"
,(char *)"how"
,(char *)"however"
,(char *)"i"
,(char *)"if"
,(char *)"important"
,(char *)"in"
,(char *)"interest"
,(char *)"interested"
,(char *)"interesting"
,(char *)"interests"
,(char *)"into"
,(char *)"is"
,(char *)"it"
,(char *)"its"
,(char *)"itself"
,(char *)"j"
,(char *)"just"
,(char *)"k"
,(char *)"keep"
,(char *)"keeps"
,(char *)"kind"
,(char *)"knew"
,(char *)"know"
,(char *)"known"
,(char *)"knows"
,(char *)"l"
,(char *)"large"
,(char *)"largely"
,(char *)"last"
,(char *)"later"
,(char *)"latest"
,(char *)"least"
,(char *)"less"
,(char *)"let"
,(char *)"lets"
,(char *)"like"
,(char *)"likely"
,(char *)"long"
,(char *)"longer"
,(char *)"longest"
,(char *)"m"
,(char *)"made"
,(char *)"make"
,(char *)"making"
,(char *)"man"
,(char *)"many"
,(char *)"may"
,(char *)"me"
,(char *)"member"
,(char *)"members"
,(char *)"men"
,(char *)"might"
,(char *)"more"
,(char *)"most"
,(char *)"mostly"
,(char *)"mr"
,(char *)"mrs"
,(char *)"much"
,(char *)"must"
,(char *)"my"
,(char *)"myself"
,(char *)"n"
,(char *)"necessary"
,(char *)"need"
,(char *)"needed"
,(char *)"needing"
,(char *)"needs"
,(char *)"never"
,(char *)"new"
,(char *)"new"
,(char *)"newer"
,(char *)"newest"
,(char *)"next"
,(char *)"no"
,(char *)"nobody"
,(char *)"non"
,(char *)"noone"
,(char *)"not"
,(char *)"nothing"
,(char *)"now"
,(char *)"nowhere"
,(char *)"number"
,(char *)"numbers"
,(char *)"o"
,(char *)"of"
,(char *)"off"
,(char *)"often"
,(char *)"old"
,(char *)"older"
,(char *)"oldest"
,(char *)"on"
,(char *)"once"
,(char *)"one"
,(char *)"only"
,(char *)"open"
,(char *)"opened"
,(char *)"opening"
,(char *)"opens"
,(char *)"or"
,(char *)"order"
,(char *)"ordered"
,(char *)"ordering"
,(char *)"orders"
,(char *)"other"
,(char *)"others"
,(char *)"our"
,(char *)"out"
,(char *)"over"
,(char *)"p"
,(char *)"part"
,(char *)"parted"
,(char *)"parting"
,(char *)"parts"
,(char *)"per"
,(char *)"perhaps"
,(char *)"place"
,(char *)"places"
,(char *)"point"
,(char *)"pointed"
,(char *)"pointing"
,(char *)"points"
,(char *)"possible"
,(char *)"present"
,(char *)"presented"
,(char *)"presenting"
,(char *)"presents"
,(char *)"problem"
,(char *)"problems"
,(char *)"put"
,(char *)"puts"
,(char *)"q"
,(char *)"quite"
,(char *)"r"
,(char *)"rather"
,(char *)"really"
,(char *)"right"
,(char *)"right"
,(char *)"room"
,(char *)"rooms"
,(char *)"s"
,(char *)"said"
,(char *)"same"
,(char *)"saw"
,(char *)"say"
,(char *)"says"
,(char *)"second"
,(char *)"seconds"
,(char *)"see"
,(char *)"seem"
,(char *)"seemed"
,(char *)"seeming"
,(char *)"seems"
,(char *)"sees"
,(char *)"several"
,(char *)"shall"
,(char *)"she"
,(char *)"should"
,(char *)"show"
,(char *)"showed"
,(char *)"showing"
,(char *)"shows"
,(char *)"side"
,(char *)"sides"
,(char *)"since"
,(char *)"small"
,(char *)"smaller"
,(char *)"smallest"
,(char *)"so"
,(char *)"some"
,(char *)"somebody"
,(char *)"someone"
,(char *)"something"
,(char *)"somewhere"
,(char *)"state"
,(char *)"states"
,(char *)"still"
,(char *)"still"
,(char *)"such"
,(char *)"sure"
,(char *)"t"
,(char *)"take"
,(char *)"taken"
,(char *)"than"
,(char *)"that"
,(char *)"the"
,(char *)"their"
,(char *)"them"
,(char *)"then"
,(char *)"there"
,(char *)"therefore"
,(char *)"these"
,(char *)"they"
,(char *)"thing"
,(char *)"things"
,(char *)"think"
,(char *)"thinks"
,(char *)"this"
,(char *)"those"
,(char *)"though"
,(char *)"thought"
,(char *)"thoughts"
,(char *)"three"
,(char *)"through"
,(char *)"thus"
,(char *)"to"
,(char *)"today"
,(char *)"together"
,(char *)"too"
,(char *)"took"
,(char *)"toward"
,(char *)"turn"
,(char *)"turned"
,(char *)"turning"
,(char *)"turns"
,(char *)"two"
,(char *)"u"
,(char *)"under"
,(char *)"until"
,(char *)"up"
,(char *)"upon"
,(char *)"us"
,(char *)"use"
,(char *)"used"
,(char *)"uses"
,(char *)"v"
,(char *)"very"
,(char *)"w"
,(char *)"want"
,(char *)"wanted"
,(char *)"wanting"
,(char *)"wants"
,(char *)"was"
,(char *)"way"
,(char *)"ways"
,(char *)"we"
,(char *)"well"
,(char *)"wells"
,(char *)"went"
,(char *)"were"
,(char *)"what"
,(char *)"when"
,(char *)"where"
,(char *)"whether"
,(char *)"which"
,(char *)"while"
,(char *)"who"
,(char *)"whole"
,(char *)"whose"
,(char *)"why"
,(char *)"will"
,(char *)"with"
,(char *)"within"
,(char *)"without"
,(char *)"work"
,(char *)"worked"
,(char *)"working"
,(char *)"works"
,(char *)"would"
,(char *)"x"
,(char *)"y"
,(char *)"year"
,(char *)"years"
,(char *)"yet"
,(char *)"you"
,(char *)"young"
,(char *)"younger"
,(char *)"youngest"
,(char *)"your"
,(char *)"yours"
,(char *)"z"};

int getExtension(char * name){
	int pos;
	if(name==NULL) return NOEXT;
	for(pos=strlen(name)-1;pos>=0;pos--){
		if(name[pos]=='.') break;
	}
	if(pos==0) return NOEXT;
	else if(strcasecmp(name+pos, ".html")==0 || strcasecmp(name+pos, ".htm")==0) return HTML;
	else if(strcasecmp(name+pos, ".ps")==0) return PS;
	else if(strcasecmp(name+pos, ".pdf")==0) return PDF;
	else if(strcasecmp(name+pos, ".xls")==0) return XLS;
	else if(strcasecmp(name+pos, ".ppt")==0) return PPT;
	else if(strcasecmp(name+pos, ".doc")==0) return DOC;
	else return NOEXT;
}

double getEngNumber(char * text){
	int pos;
	double countENG;
	const char * delim=" ,.<>#";
	if(text==NULL)
		return 0;  
	countENG=0;
	pos=0;
	while(pos<strlen(text)){
		int i;
		for(i=0;i<NSTOP_WORDS; i++){
			//if(strncasecmp(text+pos, STOP_WORDS[i], STOP_WORDS[i].length())==0)
			if(strncasecmp(text+pos, STOP_WORDS[i], strlen(STOP_WORDS[i]))==0){
				if((pos==0 || strchr(delim, text[pos-1])!=NULL) 
					&& (pos+strlen(STOP_WORDS[i])==strlen(text) 
					|| strchr(delim, text[pos+strlen(STOP_WORDS[i])]) != NULL)){
					countENG+=(double)strlen(STOP_WORDS[i])/LARGO_PAL;
				}
			}
		}
		pos++;
	}
	return countENG/(double)strlen(text);
}

void getShortUrl(char * res, char * url){
	int i=0;
	int j=0;
	if(strstr(url,"http://")==url)
		j+=7;
	while(j<strlen(url) && i<SHORT_URL_SIZE)
		res[i++]=url[j++];
	for(j=0;j<3;j++)
		res[i++]='.';
	res[i]='\0';
}

void Highlight(char * output, char * abstract) {
	int i;
	int j=0;
	
	for(i = 0; i < strlen(abstract); i++){
		if (abstract[i] == '<') {
			strcpy(output+j, START_HIGHLIGHT);
			j+=strlen(START_HIGHLIGHT);
		}
		else if (abstract[i] == '>'){
			strcpy(output+j, END_HIGHLIGHT);
			j+=strlen(END_HIGHLIGHT);
		}
		else{
			output[j++]=abstract[i];
		}
	}
	output[j]='\0';
	return;
}

void getTag(char * res, char * meta, const char * tag){
	int j;
	int i;
	int empieza;
	int termina;
	char * aux;
	size_t lMatch;
	j=0;
	aux=meta;
	empieza=0;
	termina=0;
	lMatch=(size_t)(strlen(tag)-1);
	for(i=0;i<strlen(meta) && j<MAX_TAG_SIZE; i++){
		if(strncmp(aux,tag,lMatch)==0 && !empieza && 
			(i==0 || meta[i-1]==' ' || meta[i-1]=='\3')){
			empieza=1;
			i+=(lMatch+2);
		}
		if(empieza && !termina)
			res[j++]=meta[i-1];
		if(meta[i]=='\3' && empieza)
			termina=1;
		aux++;
	}
	res[j]='\0';
	return;

}

#endif

