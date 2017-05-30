
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/msg.h>
#include <signal.h>
#include <netinet/in.h>
#include <netdb.h>
#include <stdlib.h>
#include <iostream>

#include "Query.h"
#include "Response.h"

using namespace std;


int DEFAULT_PORT = 1343;
//char DEFAULT_SERVER[5]={172,17,68,150,0};
char DEFAULT_SERVER[5]={127,0,0,1,0};


int connect(char *server, int port){
	
	struct sockaddr_in name;
	int sock;
	
	memset(&name, 0, sizeof(name));
	memcpy((char *)&name.sin_addr, server, 5);
	name.sin_family=AF_INET;
	name.sin_port=htons(port);
	sock=socket(AF_INET, SOCK_STREAM, 0);
	
	if(sock < 0){
		perror("Error opening socket");
		return -1;
	}
	if (connect(sock,(struct sockaddr *)&name,sizeof(struct sockaddr_in)) < 0){
		close(sock);
		perror("Error Connecting the socket");
		return -1;
	}
	return sock;
	
}

void cleanSuggestions(Response* &r, const char *server, int port);

int main(int argc, char **argv){
	//printf("Preparando\n");
	
	//----------Preparar Variables----------
	
	char *query=NULL;
	//char *str_query=new char[MAX_QUERY_SIZE];
	//char *highlighted=new char[1024];
	char and_or=0;
	char accents=0;
	char feedback=0;
	int doc_id=1;
	int page=0;
	int docs_page=10;
	int docs_total;
	int port=DEFAULT_PORT;
	char *server=new char[5];
	for(int i=0; i<5; i++)
		server[i]=DEFAULT_SERVER[i];
	int type=COM;
	
	//----------Recibir Argumentos----------
	//0         1     2      3       4        5      6    7         8    9  10 11 12
	//query-xml query and_or accents feedback doc_id page docs_page port s1 s2 s3 s4
	
	if(argc==1){
		//cout<<"Caso 1\n";
		query=new char[2];
		query[0]='+';
		query[1]='\0';
	}
	else if(argc==2){
		//cout<<"Caso 2\n";
		query=new char[strlen(argv[1])+2];
		query[0]='+';
		query[1]='\0';
		strcat(query, argv[1]);
	}
	else if(argc==8){
		//cout<<"Caso 3\n";
		query=new char[strlen(argv[1])+2];
		query[0]='+';
		query[1]='\0';
		strcat(query, argv[1]);
		and_or=atoi(argv[2]);
		accents=atoi(argv[3]);
		feedback=atoi(argv[4]);
		if(feedback==1) type=FEED;
		doc_id=atoi(argv[5]);
		page=atoi(argv[6]);
		docs_page=atoi(argv[7]);
	}
	else if(argc==9){
		//cout<<"Caso 3\n";
		query=new char[strlen(argv[1])+2];
		query[0]='+';
		query[1]='\0';
		strcat(query, argv[1]);
		and_or=atoi(argv[2]);
		accents=atoi(argv[3]);
		feedback=atoi(argv[4]);
		if(feedback==1) type=FEED;
		doc_id=atoi(argv[5]);
		page=atoi(argv[6]);
		docs_page=atoi(argv[7]);
		port=atoi(argv[8]);
	}
	else if(argc==13){
		//cout<<"Caso 3\n";
		query=new char[strlen(argv[1])+2];
		query[0]='+';
		query[1]='\0';
		strcat(query, argv[1]);
		and_or=atoi(argv[2]);
		accents=atoi(argv[3]);
		feedback=atoi(argv[4]);
		if(feedback==1) type=FEED;
		doc_id=atoi(argv[5]);
		page=atoi(argv[6]);
		docs_page=atoi(argv[7]);
		port=atoi(argv[8]);
		server[0]=(char)atoi(argv[9]);
		server[1]=(char)atoi(argv[10]);
		server[2]=(char)atoi(argv[11]);
		server[3]=(char)atoi(argv[12]);
	}
	else{
		//cout<<"Caso 4\n";
		printf("Usage:\n>./query-xml query and_or accents feedback doc_id page docs_page port s1 s2 s3 s4\n");
		printf("s1-s4: host\n");
		delete [] server;
		return 0;
	}
	
	if(docs_page<1)
		docs_page=1;
	if(docs_page>50)
		docs_page=50;
	
	
	int first=page*docs_page+1;
	int last=(page+1)*docs_page;
	//Este caso especial corrige un bug encontrado en el respondedor de consultas
	//no retornada la cantidad correcta de respuestas en este caso particular
	if(first<50 && last>50){
		//printf("-----------------------Consulta 50\n" );
		
		Query *q1=new Query(query, and_or, accents, type, doc_id, page, docs_page);
		Query *q2;
		//la idea es pedir lo minimo necesario
		int r=last-50;
		if(r<=2)
			q2=new Query(query, and_or, accents, type, doc_id, 25, 2);
		else if(r<=5)
			q2=new Query(query, and_or, accents, type, doc_id, 10, 5);
		else if(r<=10)
			q2=new Query(query, and_or, accents, type, doc_id, 5, 10);
		else if(r<=25)
			q2=new Query(query, and_or, accents, type, doc_id, 2, 25);
		else
			q2=new Query(query, and_or, accents, type, doc_id, 1, 50);
		
		Response *r1=new Response();
		Response *r2=new Response();
		
		int connection1, connection2;
		connection1=connect(server, port);
		connection2=connect(server, port);
		
		if(connection1<0 || connection1<0){
			perror("Can't connect");
			r1->setQuery(query, and_or, accents, feedback);
			r1->error=1;
		}
		else{
			q1->execute(r1, connection1);
			q2->execute(r2, connection2);
			close(connection1);
			close(connection2);
			//agregamos las (last-50) primeras referencias
			r1->addResults(r2->results, r);
		}
		
		r1->printXml();
	
		delete q1;
		delete q2;
		delete r1;
		delete r2;
		
		
	}
	else{
		//printf("-----------------------Consulta Correcta\n" );
		
		Query *q=new Query(query, and_or, accents, type, doc_id, page, docs_page);
		Response *r=new Response();
	
		int connection;
		connection=connect(server, port);
	
		if(connection<0){
			perror("Can't connect");
			r->setQuery(query, and_or, accents, feedback);
			r->error=1;
		}
		else{
			q->execute(r, connection);
			cleanSuggestions(r, server, port);
			close(connection);
		}
		
		
		r->printXml();
	
		delete q;
		delete r;
		
	}
	
	delete [] query;
	delete [] server;
	
	return 0;
}

void cleanSuggestions(Response* &r, const char *server, int port){
	//printf("cleanSuggestions - Inicio\n");
	//printf("tiene %i sugerencias\n", r->suggestions_count);
	bool bad_suggestion=false;
	Query *q;
	Response *res;
	int connection;
	for(int i=0; i<r->suggestions_count; i++){
		//Verificar sugerencia i
		//printf("Verificando \"%s\"\n", r->suggestions[i].text);
		
		q=new Query(r->suggestions[i].text, 0, 0, COM, 0, 0, 0);
		res=new Response();
		
		connection=connect((char *)server, port);
		if(connection<0){
			perror("Can't connect");
			//r->setQuery(query, and_or, accents, feedback);
			//r->error=1;
		}
		else{
			q->execute(res, connection);
			close(connection);
		}
		//printf("%i resultados\n", res->results_count);
		if(res->results_count<1){
			r->bad_suggestions[i]=true;
		}
		delete q;
		delete res;
	}
	
	//printf("cleanSuggestions - Fin\n");
}

