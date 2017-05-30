#if !defined(_QUERY_H)
#define _QUERY_H

#include "Response.h"

#define TODOS_DOMINIOS 0xFFFFFFFF
#define TODOS_TEMAS 0x80000000UL
#define FEED 0
#define COM 1

class Query{
public : 
	int text_length;
	char *text;
	int and_or;
	int accents;
	int doc_id;
	int page;
	int docs_page;
	int type;
	int place;
	int theme;
	int domain;
	
	Query(const char *_text, int _and_or, int _accents, 
		int _type, int _doc_id, int _page, int _docs_page){
		text_length=strlen(_text);
		text=new char[text_length+1];
		strcpy(text, _text);
		and_or=_and_or;
		accents=_accents;
		doc_id=_doc_id;
		page=_page;
		docs_page=_docs_page;
		type=_type;
		//Los siguientes quedan fijos
		place=255;
		theme=TODOS_TEMAS;
	}
	
	~Query(){
		//printf("~Query\n");
		delete [] text;
	}
	
	//Response* execute(int connection){
	int execute(Response* &r, int connection){
		
		if(sendQuery(connection)>0){
			r->error=2;
			return r->error;
			//return r;
		}
		if(reciveResponse(r, connection)>0){
			r->error=3;
			return r->error;
			//return r;
		}
		
		//Datos de la Query
		if(type==FEED)
			r->setQuery(text, and_or, accents, 1);
		else
			r->setQuery(text, and_or, accents, 0);
		
		//Datos adcionales de Response
		for(int i=0; i<r->results_count; i++){
			r->results[i].rank=page*docs_page+i+1;
		}
		
		r->from=page*docs_page+1;
		
		if(r->total < (page+1)*docs_page)
			r->to=r->total;
		else
			r->to=(page+1)*docs_page;
		
		return r->error;
		//return r;
	}
	
	int sendQuery(int connection){
		if(type==COM){
			
			if (write(connection, &type, sizeof(int)) < 0){
				perror("Error writing size of query");
				return 1;
			}
			int queryLength =text_length+1;
			if (write(connection, &queryLength, sizeof(int)) < 0){
				perror("Error writing size of query");
				return 1;
			}
			
			if (write(connection, text, queryLength*sizeof(char)) < 0){
				perror("Error writing query");
				return 1;
			}
			if (write(connection, &and_or, sizeof(char)) < 0){
				perror("Error writing metodo (and_or)");
				return 1;
			}
			if (write(connection, &accents, sizeof(char)) < 0){
				perror("Error writing accents");
				return 1;
			}
			if (write(connection, &docs_page, sizeof(int)) < 0){
				perror("Error writing docs_page");
				return 1;
			}
			if (write(connection, &page, sizeof(int)) < 0){
				perror("Error writing page");
				return 1;
			}
			if (write(connection, &place, sizeof(int)) < 0){
				perror("Error writing place");
				return 1;
			}
			if (write(connection, &theme, sizeof(unsigned long)) < 0){
				perror("Error writing tema");
				return 1;
			}
		}
		else if(type==FEED){
			//printf("make_query -Escribiendo\n");
			if (write(connection, &type, sizeof(int)) < 0){
				perror("Error writing size of query");
				return 1;
			}
			if (write(connection, &doc_id, sizeof(int)) < 0){
				perror("Error writing size of doc_id");
				return 1;
			}
			if (write(connection, &docs_page, sizeof(int)) < 0){
				perror("Error writing docs_page");
				return 1;
			}
			if (write(connection, &page, sizeof(int)) < 0){
			 perror("Error writing page");
			 return 1;
			}
			if (write(connection, &place, sizeof(int)) < 0){
				perror("Error writing size of place");
				return 1;
			}
			if (write(connection, &theme, sizeof(unsigned long)) < 0){
				perror("Error writing size of tema");
				return 1;
			}
		}
		else{
			perror("Unsupported Query Type");
			return 1;
		}
		return 0;
	}
	
	int reciveResponse(Response* &r, int connection){
		int string_length, read_length;
		
		if(type==COM){
		
			//printf("make_query - Recibiendo\n");
			if (recv(connection, &(r->suggestions_count) , sizeof(int), MSG_WAITALL ) < 0){
			//if (read(connection, &(r->suggestions_count), sizeof(int)) < 0){
				perror("Error reading document title size");
				return 1;
			}
			
			for (int i=0; i < r->suggestions_count; i++){
				if (recv(connection, &string_length, sizeof(int),MSG_WAITALL ) < 0){
				//if (read(connection, &string_length, sizeof(int)) < 0){
					perror("Error reading suggestion size");
					return 1;
				}
				r->suggestions[i].text_length=string_length;
				read_length = recv(connection, (void *)(r->suggestions[i].text),	
					string_length*sizeof(char),MSG_WAITALL);
				//read_length = read(connection, (r->suggestions[i].text), string_length*sizeof(char));
				if(read_length != string_length){
					perror("Error reading suggestion");
					return 1;
				}
			}

			//Numero maximo de documentos visibles (max = 200)
			if (read(connection, &r->total, sizeof(int)) != sizeof(int)){
				perror("Error reading answer size");
				return(1);
			}
			//Numero de resultados en esta pagina (max = 50)
			if(read(connection, &r->results_count, sizeof(int)) != sizeof(int)){
				perror("Error reading answer size");
				return(1);
			}
			
			//printf("-----------------------%d\n", r->results_count);
		
			//results=new Result[nResults];
			for(int i = 0; i < r->results_count; i++){
				if (read(connection, &r->results[i].doc_id, sizeof(int)) < 0){
					perror("Error reading document id");
					return 1;
				}
				if (read(connection, &r->results[i].sim, sizeof(float)) < 0){
					perror("Error reading document id");
					return 1;
				 }
			 }
			 
			for(int i = 0; i < r->results_count; i++){
				if (recv(connection, (void *)(&r->results[i].type), sizeof(char), MSG_WAITALL) < 0){
				//if (read(connection, (&r->results[i].type), sizeof(char)) < 0){
					perror("Error reading document type");
					return 1;
				}
				if (recv(connection, (void *)(&r->results[i].base_id), sizeof(int), MSG_WAITALL) < 0){
				//if (read(connection, (&r->results[i].base_id), sizeof(int)) < 0){
					perror("Error reading document baseid");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int), MSG_WAITALL) < 0){
				//if (read(connection, &string_length, sizeof(int)) < 0){
					perror("Error reading document title size");
					return 1;
				}
				r->results[i].title_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].title), string_length*sizeof(char), MSG_WAITALL);
				//read_length = read(connection, (r->results[i].title), string_length*sizeof(char));
				if(read_length != string_length){
					perror("Error reading document title");
					return 1;
				}
				if(recv(connection, &string_length, sizeof(int), MSG_WAITALL) < 0){
				//if(read(connection, &string_length, sizeof(int)) < 0){
					perror("Error readiSugs->NumElemng document url size");
					return 1;
				}
				r->results[i].url_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].url), string_length*sizeof(char), MSG_WAITALL);
				//read_length = read(connection, (r->results[i].url), string_length*sizeof(char));
				if(read_length != string_length){
					perror("Error reading document url");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int),MSG_WAITALL) < 0){
				//if (read(connection, &string_length, sizeof(int)) < 0){
					perror("Error reading document summary size");
					return 1;
				}
				r->results[i].summary_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].summary),	string_length*sizeof(char), MSG_WAITALL);
				//read_length = read(connection, (r->results[i].summary),	string_length*sizeof(char));
				if(read_length != string_length){
					perror("Error reading document summary");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int),MSG_WAITALL) < 0){
				//if (read(connection, &string_length, sizeof(int)) < 0){
					perror("Error reading document meta size");
					return 1;
				}
				r->results[i].meta_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].meta),	string_length*sizeof(char), MSG_WAITALL);
				//read_length = read(connection, (r->results[i].meta), string_length*sizeof(char));
				if(read_length != string_length){
					perror("Error reading document meta");
					return 1;
				}
			}//for... cada result
		
		}
		else if(type==FEED){
		
			//Numero maximo de documentos visibles (max = 200)
			if (read(connection, &r->total, sizeof(int)) != sizeof(int)){
				perror("Error reading answer size");
				return(1);
			}
	
			//Numero de resultados en esta pagina (max = 50)
			if (read(connection, &r->results_count, sizeof(int)) != sizeof(int)){
				perror("Error reading answer size");
				return(1);
			}
			//printf("-----------------------%d-----------------------\n". r->results_count);
			//results=new Result[nResults];
			for(int i=0; i<r->results_count; i++){
				if (read(connection, (void *)&r->results[i].doc_id, sizeof(int)) < 0){
					perror("Error reading document id");
					return 1;
				}
				if (read(connection, (void *)&r->results[i].sim, sizeof(float)) < 0){
					perror("Error reading document sim");
					return 1;
				}
			}
		
			for(int i=0; i<r->results_count; i++){
				 if (recv(connection, (void *)(&r->results[i].type), sizeof(char), MSG_WAITALL) < 0){
					perror("Error reading document type");
					return 1;
				}
				if (recv(connection, (void *)(&r->results[i].base_id), sizeof(int), MSG_WAITALL) < 0){
					perror("Error reading document baseid");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int), MSG_WAITALL ) < 0){
					perror("Error reading document title size");
					return 1;
				}
				r->results[i].title_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].title), string_length*sizeof(char), MSG_WAITALL);
				if(read_length != string_length){
					perror("Error reading document title");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int), MSG_WAITALL) < 0){
					perror("Error reading document url size");
					return 1;
				}
				r->results[i].url_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].url), string_length*sizeof(char), MSG_WAITALL);
				if(read_length != string_length){
					perror("Error reading document url");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int), MSG_WAITALL) < 0){
					perror("Error reading document abstract size");
					return 1;
				}
				r->results[i].summary_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].summary), string_length*sizeof(char), MSG_WAITALL);
				if(read_length != string_length) {
					perror("Error reading document summary");
					return 1;
				}
				if (recv(connection, &string_length, sizeof(int), MSG_WAITALL) < 0){
					perror("Error reading document tag size");
					return 1;
				}
				r->results[i].meta_length=string_length;
				read_length = recv(connection, (void *)(r->results[i].meta), string_length*sizeof(char), MSG_WAITALL);
				if(read_length != string_length) {
					perror("Error reading document meta");
					return 1;
				}
			}//for.. cada result
		
		}
		else{
			perror("Unsupported Query Type");
			return 1;
		}
		
		return 0;
		
	}
	
};

#endif

