#if !defined(_RESPONSE_H)
#define _RESPONSE_H

#include "Result.h"
#include "Suggestion.h"
#include "util.h"


#define MAX_RESULTS 50
#define MAX_SUGGESTIONS 3
#define MAX_STRING 1000

class Response{
public : 
	Result *results;
	int results_count;
	Suggestion *suggestions;
	int suggestions_count;
	bool *bad_suggestions;
	int from;
	int to;
	int total;
	int error;
	char *query;
	int and_or;
	int accents;
	int feedback; 
	Response(){
		results=new Result[MAX_RESULTS];
		suggestions=new Suggestion[MAX_SUGGESTIONS];
		bad_suggestions=new bool[MAX_SUGGESTIONS];
		for(int i=0; i<MAX_SUGGESTIONS; i++)
			bad_suggestions[i]=false;
		from=0;
		to=0;
		total=0;
		error=0;
		query=NULL;
		and_or=0;
		accents=0;
		feedback=0;
	}
	~Response(){
		//printf("~Response\n");
		delete [] results;
		delete [] suggestions;
		if(query!=NULL)
			delete [] query;
	}
	
	void addResults(Result *results2, int n){
		for(int i=0; i<n; i++){
			//printf("-----------------------Agregando %s a %d\n", results2[i].title, i+results_count);
			results[i+results_count].url_length=results2[i].url_length;
			strcpy(results[i+results_count].url, results2[i].url);
			results[i+results_count].title_length=results2[i].title_length;
			strcpy(results[i+results_count].title, results2[i].title);
			results[i+results_count].summary_length=results2[i].summary_length;
			strcpy(results[i+results_count].summary, results2[i].summary);
			results[i+results_count].meta_length=results2[i].meta_length;
			strcpy(results[i+results_count].meta, results2[i].meta);
			results[i+results_count].type=results2[i].type;
			results[i+results_count].base_id=results2[i].base_id;
			results[i+results_count].size=results2[i].size;
			results[i+results_count].doc_id=results2[i].doc_id;
			results[i+results_count].sim=results2[i].sim;
			results[i+results_count].rank=results2[i].rank;
		}
		results_count+=n;
	}
	
	void setQuery(const char *_query, int _and_or, int _accents, int _feedback){
		query=new char[strlen(_query)+1];
		strcpy(query, _query);
		
		and_or=_and_or;
		accents=_accents;
		feedback=_feedback;
	}
	
	void printXml(){
	
		char *string=new char[MAX_STRING];
	
		//--------------------Response--------------------
	
		printf("<RESPONSE ERROR=\"%d\">\n", error);
	
		//--------------------Consulta--------------------
	
		printf("<QUERY AND_OR=\"%d\" ACCENTS=\"%d\" FEEDBACK=\"%d\">\n", and_or, accents, feedback);
		
		if(query!=NULL){
			strcpy(string, query);
			
			//Cambiar '+' por ' '
			char *caracter;
			caracter=strchr(string,'+');
			while(caracter != NULL){
				*caracter=' ';
				caracter=strchr(string,'+'); 
			}
			printf("<TEXT>%s</TEXT>\n", string);
			
			}
		else
			printf("<TEXT> </TEXT>\n");
			
		printf("</QUERY>\n");
			
	
		//--------------------Sugerencias--------------------
		int good_suggestions=suggestions_count;
		for(int i=0; i<MAX_SUGGESTIONS; i++){
			if(bad_suggestions[i])
				good_suggestions--;
		}
		printf("<SUGGESTIONS TOTAL=\"%d\">\n", good_suggestions);
		for(int i=0; i<MAX_SUGGESTIONS; i++){
			if(!bad_suggestions[i] && good_suggestions){
				printf("<SUGGESTION>\n");
				printf("<NEW_QUERY>%s</NEW_QUERY>\n", suggestions[i].text);
				printf("</SUGGESTION>\n");
			}
		}
		
		printf("</SUGGESTIONS>\n");
		
		//--------------------Resultados--------------------
		
		printf("<RESULTS FROM=\"%d\" TO=\"%d\" TOTAL=\"%d\">\n", from, to, total);
		
		//--------------------Para cada resultado--------------------
		
		for(int i=0; i<results_count; i++){
			printf("<RESULT RANK=\"%d\" DOC_ID=\"%d\">\n", results[i].rank, results[i].doc_id);
			
			//--------------------MEDIA TYPE--------------------
			int type=getExtension(results[i].url);
			switch (type){
				case HTML: printf("<TYPE>HTML</TYPE>\n"); break;
				case PS: printf("<TYPE>PS</TYPE>\n"); break;
				case PDF: printf("<TYPE>PDF</TYPE>\n"); break;
				case XLS: printf("<TYPE>XLS</TYPE>\n"); break;
				case PPT: printf("<TYPE>PPT</TYPE>\n"); break;
				case DOC: printf("<TYPE>DOC</TYPE>\n"); break;
				default: printf("<TYPE>UNKNOWN</TYPE>\n"); break;
			}
		
			//--------------------TITULO--------------------
			if(strcmp(results[i].title,"")!=0){
				Highlight(string, results[i].title);
				printf("<TITLE><![CDATA[%s]]></TITLE>\n", string);
			}
			else{
				getShortUrl(string, results[i].url);
				printf("<TITLE><![CDATA[%s]]></TITLE>\n", string);
			}

			//--------------------LANG--------------------
			double engNum = getEngNumber(results[i].summary);
			if(engNum > ENG_CRITERIA){
				printf("<LANGUAGE>ENGLISH</LANGUAGE>\n");
			}
			else{
				printf("<LANGUAGE>SPANISH</LANGUAGE>\n");
			}
		
			//--------------------SUMARIO--------------------
			Highlight(string, results[i].summary);
			printf("<SUMMARY><![CDATA[%s]]></SUMMARY>\n", string);
	
			//--------------------URL--------------------
			printf("<URL><![CDATA[%s]]></URL>\n", results[i].url);
			//Podria eliminarse este campo
			getShortUrl(string, results[i].url);
			printf("<SHORT_URL><![CDATA[%s]]></SHORT_URL>\n", string);

			//--------------------TAMAÑO--------------------
			getTag(string, results[i].meta, "largo=");
			results[i].size=atoi(string);
			//El tamaño se envia en bytes
			printf("<SIZE>%d</SIZE>\n", results[i].size);
		
			printf("</RESULT>\n");
		
		} //for... cada resultado
	
		printf("</RESULTS>\n");
		
			
		
		printf("</RESPONSE>\n");
		
		delete [] string;
		
	}
	
	
};

#endif

