
#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <time.h>
#include <string.h>
#include <fstream>
#include <iostream>
#include <string.h>
#include <map>
#include <list>

#include "Lector.h"

using namespace std;

void millisleep( unsigned int milliseconds )
{
# ifdef _MSC_VER   // If its Visual C++ call Win32 Sleep function
   ::Sleep( milliseconds);
#else  // Else assume a UNIX/Linux system with nanosleep function
   timespec ts;
   ts.tv_sec = milliseconds / 1000;
   ts.tv_nsec = (milliseconds - ts.tv_sec*1000) * 1000000;
   ::nanosleep(&ts, NULL);
# endif
}

inline bool es_letra_estricto(int c){
	if(c>='A' && c<='Z'
	|| c>='a' && c<='z'){
		return true;
	}
	return false;
}

inline bool es_letra_numero(int c){
	if(c>='A' && c<='Z'
	|| c>='a' && c<='z'
	|| c>='0' && c<='9'){
		return true;
	}
	return false;
}

inline bool eliminar_especial(char *s){
	if( !es_letra_numero(s[0]) ){
		return true;
	}
	return false;
}

inline bool eliminar_rapido_estricto(char *s){
	int largo=strlen(s);
	for(int i=0; i<largo; i++){
		if(!es_letra_estricto(s[i]))
			return true;
	}
	return false;
}

inline void minusculas(char *s, char **tabla){
	int largo=strlen(s);
	for(int i=0; i<largo; i++){
		if(s[i]>='A' && s[i]<='Z')
			s[i]=tabla[s[i]-'A'][0];
	}
}

int calcular_largo_archivo(char *archivo){
	ifstream lector(archivo, ifstream::in);
	if(lector.good()){
		lector.seekg(0, ios::end);
  		int largo=lector.tellg();
  		lector.close();
  		return largo;
	}
	else{
		return 0;
	}
}

int main(int argc, char* argv[]){
	
	if(argc!=5){
		cout<<"Modo de uso\n>./lista ruta_textos ruta_listas id_grupo numero_textos\n";
		return 0;
	}
	const char *ruta_textos=argv[1];
	const char *ruta_listas=argv[2];
	int id_grupo=atoi(argv[3]);
	int numero_textos=atoi(argv[4]);
	
	char *archivo=new char[255];
	char *palabra=new char[201];
	string *s;
	
	map<string, map<int, int> > *frecuencias;
	map<string, map<int, int> >::iterator it_s;
	map<int, int>::iterator it_f;
	map<string, list<int> > lista;
	map<string, list<int> >::iterator it_lista;
	list<int>::iterator it_i;
	
	Lector *lector=new Lector("");
	//id_doc final de 600-1000: 158655
	//id_doc final de 1001-1300: 418281
	//id_doc final de 1301-1600: 1252225
	lector->id_doc=0+1;
	lector->maximo_palabra=200;
	
	bool binario=false;
	
	int lectura;
	int errores;
	int largo_archivo;
	
	cout<<"Inicio (";
	if(binario)
		cout<<"binario, ";
	else
		cout<<"texto, ";
	cout<<"id_inicial: "<<lector->id_doc<<"";
	cout<<")\n";
	
	clock_t inicio, fin;
	inicio=clock();
	
	char **tabla_caracteres=new char*[26];
	for(int i=0; i<26; i++){
		tabla_caracteres[i]=new char[2];
	}
	int n=0;
	tabla_caracteres[n][0]='a';
	tabla_caracteres[n++][1]='A';
	tabla_caracteres[n][0]='b';
	tabla_caracteres[n++][1]='B';
	tabla_caracteres[n][0]='c';
	tabla_caracteres[n++][1]='C';
	tabla_caracteres[n][0]='d';
	tabla_caracteres[n++][1]='D';
	tabla_caracteres[n][0]='e';
	tabla_caracteres[n++][1]='E';
	tabla_caracteres[n][0]='f';
	tabla_caracteres[n++][1]='F';
	tabla_caracteres[n][0]='g';
	tabla_caracteres[n++][1]='G';
	tabla_caracteres[n][0]='h';
	tabla_caracteres[n++][1]='H';
	tabla_caracteres[n][0]='i';
	tabla_caracteres[n++][1]='I';
	tabla_caracteres[n][0]='j';
	tabla_caracteres[n++][1]='J';
	tabla_caracteres[n][0]='k';
	tabla_caracteres[n++][1]='K';
	tabla_caracteres[n][0]='l';
	tabla_caracteres[n++][1]='L';
	tabla_caracteres[n][0]='m';
	tabla_caracteres[n++][1]='M';
	tabla_caracteres[n][0]='n';
	tabla_caracteres[n++][1]='N';
	tabla_caracteres[n][0]='o';
	tabla_caracteres[n++][1]='O';
	tabla_caracteres[n][0]='p';
	tabla_caracteres[n++][1]='P';
	tabla_caracteres[n][0]='q';
	tabla_caracteres[n++][1]='Q';
	tabla_caracteres[n][0]='r';
	tabla_caracteres[n++][1]='R';
	tabla_caracteres[n][0]='s';
	tabla_caracteres[n++][1]='S';
	tabla_caracteres[n][0]='t';
	tabla_caracteres[n++][1]='T';
	tabla_caracteres[n][0]='u';
	tabla_caracteres[n++][1]='U';
	tabla_caracteres[n][0]='v';
	tabla_caracteres[n++][1]='V';
	tabla_caracteres[n][0]='w';
	tabla_caracteres[n++][1]='W';
	tabla_caracteres[n][0]='x';
	tabla_caracteres[n++][1]='X';
	tabla_caracteres[n][0]='y';
	tabla_caracteres[n++][1]='Y';
	tabla_caracteres[n][0]='z';
	tabla_caracteres[n++][1]='Z';
	
	//Fijar un archivo
	for(int k=1; k<=numero_textos; k++){
		//cout<<"0\n";
		if(ruta_textos[strlen(ruta_textos)-1]=='/')
			sprintf(archivo, "%stext_%i_%i", ruta_textos, id_grupo, k);
		else
			sprintf(archivo, "%s/text_%i_%i", ruta_textos, id_grupo, k);
		
		//cout<<"1 ("<<archivo<<")\n";
		lector->reiniciar(archivo);
		//cout<<"2\n";
		frecuencias=new map<string, map<int, int> >();
		
		//cout<<"3\n";
		if(lector->estado!=EOF){
			//cout<<"4\n";
			largo_archivo=calcular_largo_archivo(archivo);
			if(largo_archivo<1024)
				cout<<"\nLeyendo "<<archivo<<" ("<<largo_archivo<<" B)\n";
			else if(largo_archivo<1024*1024)
				cout<<"\nLeyendo "<<archivo<<" ("<<((float)largo_archivo/1024)<<" KB)\n";
			else
				cout<<"\nLeyendo "<<archivo<<" ("<<((float)largo_archivo/(1024*1024))<<" MB)\n";
			while(lector->estado!=EOF){
				
				//Esquema 1: Omitiendo Rapido
				lectura=lector->palabra(palabra);
				if(lector->estado==EOF){
					cout<<"El archivo terminio bien\n";
					break;
				}
				else if(lectura<1 || lectura>20){
					//cout<<"[Descartada] por largo\n";
				}
				else if(eliminar_especial(palabra)){
					//cout<<"[Descartada] por errores\n";
				}
				else{
					minusculas(palabra, tabla_caracteres);
					s=new string(palabra);
					((*frecuencias)[*s])[lector->id_doc]++;
					delete s;
				}
				
			}//while... no EOF
			
			//cout<<"Examinando Frecuencias\n";
			cout<<"Agregando "<<frecuencias->size()<<" palabras... ";
			for(it_s=frecuencias->begin(); it_s!=frecuencias->end(); it_s++){
				//cout<<(*it_s).first<<" (en "<<((*it_s).second).size()<<" docs)\n";
				for(it_f=((*it_s).second).begin(); it_f!=((*it_s).second).end(); it_f++){
					(lista[(*it_s).first]).push_back((*it_f).first);
					//cout<<"-- doc["<<(*it_f).first<<"]: "<<(*it_f).second<<"\n";
				}
			}
			cout<<lista.size()<<" palabras en total\n";
		
		}//if... el lector es bueno
		
		//Eliminar las frecuencias para volver a empezar
		delete frecuencias;
		
	}//for... cada archivo
	
	cout<<"----- Ultimo id_doc: "<<lector->id_doc<<" -----\n";
	
	delete lector;
	
	cout<<"Terminando...\n";
	
	//Escribir resultado
	fstream *escritor;
	char *linea=new char[255];
	if(binario){
		//El formato es:
		//[int numero_palabras][int largo_palabra][char* palabra][int largo_lista][int l1][int l2]...[int ln][...se repite...]
		if(ruta_listas[strlen(ruta_listas)-1]=='/')
			sprintf(linea, "%slista_binaria_%i", ruta_listas, id_grupo);
		else
			sprintf(linea, "%s/lista_binaria_%i", ruta_listas, id_grupo);
		cout<<"[Binario] - Escribiendo en \""<<linea<<"\"\n";
		escritor=new fstream(linea, fstream::trunc | fstream::binary | fstream::out);
	}
	else{
		if(ruta_listas[strlen(ruta_listas)-1]=='/')
			sprintf(linea, "%slista_texto_%i.txt", ruta_listas, id_grupo);
		else
			sprintf(linea, "%s/lista_texto_%i.txt", ruta_listas, id_grupo);
		cout<<"[Texto] - Escribiendo en \""<<linea<<"\"\n";
		escritor=new fstream(linea, fstream::trunc | fstream::out);
	}
	
	map<string, int>::iterator it;
	
	if(binario){
		n=lista.size();
		//son n palabras
		escritor->write((char*)(&n), sizeof(int)/sizeof(char));
	}
	
	for(it_lista=lista.begin(); it_lista!=lista.end(); it_lista++){
		//cout<<(*it_lista).first<<"(";
		if(binario){
			n=((*it_lista).first).size();
			escritor->write((char*)(&n), sizeof(int)/sizeof(char));
			escritor->write(((*it_lista).first).data(), n);
			n=((*it_lista).second).size();
			escritor->write((char*)(&n), sizeof(int)/sizeof(char));
		}
		else{
			escritor->write(((*it_lista).first).data(), ((*it_lista).first).size());
		}
		
		for(it_i=((*it_lista).second).begin(); it_i!=((*it_lista).second).end(); it_i++){
			//cout<<" "<<*it_i<<" ";
			if(binario){
				n=(*it_i);
				escritor->write((char*)(&n), sizeof(int)/sizeof(char));
			}
			else{
				sprintf(linea, " %i", *it_i);
				escritor->write(linea, strlen(linea));
			}
		}
		//cout<<")\n";
		if(binario){
			//Sin separador
		}
		else{
			sprintf(linea, " \n");
			escritor->write(linea, strlen(linea));
		}
		
	}
	
	delete linea;
	escritor->close();
	delete escritor;
	
	cout<<"Terminando...\n";
	
	fin=clock();
	cout<<"Tiempo: "<<((double)fin-inicio)/CLOCKS_PER_SEC<<"\n";
	
	delete [] archivo;
	delete [] palabra;
	delete [] tabla_caracteres;
	
	
	cout<<"Fin\n";
	
}



