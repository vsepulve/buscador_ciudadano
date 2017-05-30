
#if !defined(_LECTOR_H)
#define _LECTOR_H

#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <float.h>
#include <typeinfo>
#include <math.h>
#include <time.h>
#include <string.h>
#include <fstream>
#include <iostream>
#include <set>

using namespace std;

class Lector{
public:
	char *archivo;
	bool debug;
	ifstream *lector;
	int id_doc;
	int estado;
	set<char> *separadores;
	int maximo_palabra;
	Lector(const char *_archivo){
		
		separadores=new set<char>();
		separadores->insert(' ');
		separadores->insert('\n');
		separadores->insert('.');
		separadores->insert(',');
		separadores->insert(':');
		separadores->insert(';');
		separadores->insert('_');
		separadores->insert('-');
		separadores->insert('{');
		separadores->insert('}');
		separadores->insert('[');
		separadores->insert(']');
		separadores->insert('(');
		separadores->insert(')');
		separadores->insert('\"');
		separadores->insert('\'');
		separadores->insert('\\');
		separadores->insert('/');
		separadores->insert('#');
		separadores->insert('%');
		separadores->insert('~');
		separadores->insert('|');
		separadores->insert('=');
		separadores->insert('<');
		separadores->insert('>');
		
		archivo=new char[strlen(_archivo)+1];
		strcpy(archivo, _archivo);
		lector=new ifstream(archivo, ifstream::in);
		debug=true;
		id_doc=1;
		estado=0;
		maximo_palabra=100;
		if(!lector->good()){
			estado=EOF;
		}
	}
	~Lector(){
		if(archivo!=NULL){
			delete [] archivo;
		}
		if(lector!=NULL){
			lector->close();
			delete lector;
		}
		delete separadores;
	}
	void reiniciar(const char *_archivo){
		//cout<<"reiniciando con \""<<_archivo<<"\" ("<<strlen(_archivo)<<" letras)\n";
		if(archivo!=NULL){
			delete [] archivo;
		}
		if(lector!=NULL){
			lector->close();
			delete lector;
		}
		archivo=new char[strlen(_archivo)+1];
		strcpy(archivo, _archivo);
		lector=new ifstream(archivo, ifstream::in);
		debug=true;
		//Continuaremos los ids
		//id_doc=0;
		estado=0;
		if(!lector->good()){
			estado=EOF;
		}
		
	}
	int palabra(char *linea){
		int contador=0;
		int c;
		while(true){
			if(!lector->good()){
				estado=EOF;
				id_doc++;
				return 0;
			}
			c=lector->get();
			//cout<<"char: \""<<(char)c<<"\", int: \""<<(int)c<<"\"\n";
			if(c==0){
				//se ha llegado al fin de un documento
				id_doc++;
				break;
			}
			else if(c==EOF){
				//Se ha llegado a EOF
				id_doc++;
				estado=EOF;
				break;
			}
			else if(separadores->find(c)!=separadores->end()){
				//Se ha llegado a fin de esta palabra
				//cout<<"No es letra\n";
				break;
			}
			else{
				linea[contador]=(char)c;
				contador++;
				if(contador>maximo_palabra){
					break;
				}
			}
		}
		linea[contador]=0;
		//printf("Palabra: \"%s\"\n", linea);
		return contador;
		
	}
	
};

#endif

