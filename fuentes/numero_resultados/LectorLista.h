
#if !defined(_LECTOR_LISTA_H)
#define _LECTOR_LISTA_H

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

class LectorLista{
public:
	char *archivo;
	ifstream *lector;
	//estado=0 => Ok
	//estado=1 => Fin de Linea
	//estado=EOF => Fin o lector malo
	int estado;
	int maximo_palabra;
	LectorLista(const char *_archivo){
		archivo=new char[strlen(_archivo)+1];
		strcpy(archivo, _archivo);
		lector=new ifstream(archivo, ifstream::in);
		estado=0;
		maximo_palabra=100;
		if(!lector->good()){
			estado=EOF;
		}
	}
	~LectorLista(){
		if(archivo!=NULL){
			delete [] archivo;
		}
		if(lector!=NULL){
			lector->close();
			delete lector;
		}
	}
	void reiniciar(const char *_archivo){
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
		estado=0;
		if(!lector->good()){
			estado=EOF;
		}
		
	}
	void saltar_linea(){
		int c;
		while(true){
			if(!lector->good()){
				estado=EOF;
				return;
			}
			c=lector->get();
			if(c=='\n'){
				//se ha llegado al fin de una linea
				estado=1;
				return;
			}
		}
	}
	int palabra(char *linea){
		//El estado 1 solo dura hasta la proxima lectura
		if(estado==1)
			estado=0;
		int contador=0;
		int c;
		while(true){
			if(!lector->good()){
				estado=EOF;
				return 0;
			}
			c=lector->get();
			if(c=='\n'){
				//se ha llegado al fin de una linea
				estado=1;
				break;
			}
			else if(c==EOF){
				//Se ha llegado a EOF
				estado=EOF;
				break;
			}
			else if(c==' '){
				//Se ha llegado a fin de esta palabra
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

