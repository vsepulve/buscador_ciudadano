
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
#include <string.h>
#include <map>
#include <list>

#include "LectorLista.h"

void millisleep(unsigned int milliseconds){
#ifdef _MSC_VER   // If its Visual C++ call Win32 Sleep function
	::Sleep( milliseconds);
#else  // Else assume a UNIX/Linux system with nanosleep function
	timespec ts;
	ts.tv_sec = milliseconds / 1000;
	ts.tv_nsec = (milliseconds - ts.tv_sec*1000) * 1000000;
	::nanosleep(&ts, NULL);
#endif
}

inline int cargar_palabras(const char *consulta, char ** &palabras){
	//cout<<"(cargar_palabras) - inicio\n";
	int inicio, fin;
	if(consulta[0]=='+'){
		inicio=1;
	}
	else{
		inicio=0;
	}
	if(consulta[strlen(consulta)-1]=='+'){
		fin=strlen(consulta)-1;
	}
	else{
		fin=strlen(consulta);
	}
	//contar palabras
	int numero_palabras=1;
	for(int i=inicio; i<fin; i++){
		if(consulta[i]=='+'){
			numero_palabras++;
		}
	}
	//cout<<numero_palabras<<" palabras\n";
	int *largo_palabras=new int[numero_palabras];
	palabras=new char*[numero_palabras];
	
	//contar largos de palabras
	for(int i=0; i<numero_palabras; i++){
		largo_palabras[0]=0;
	}
	int palabra_actual=0;
	for(int i=inicio; i<fin; i++){
		if(consulta[i]=='+'){
			palabra_actual++;
		}
		else{
			largo_palabras[palabra_actual]++;
		}
	}
	
	for(int i=0; i<numero_palabras; i++){
		//cout<<"Largo palabra "<<i<<": "<<largo_palabras[i]<<"\n";
		palabras[i]=new char[largo_palabras[i]+1];
	}
	
	//copiar palabras
	palabra_actual=0;
	int contador=0;
	for(int i=inicio; i<fin; i++){
		if(consulta[i]=='+'){
			palabras[palabra_actual][contador++]=0;
			//cout<<"palabra "<<palabra_actual<<": "<<palabras[palabra_actual]<<"\n";
			contador=0;
			palabra_actual++;
		}
		else{
			palabras[palabra_actual][contador++]=consulta[i];
		}
	}
	palabras[palabra_actual][contador++]=0;
	//cout<<"palabra "<<palabra_actual<<": "<<palabras[palabra_actual]<<"\n";
	
	delete [] largo_palabras;
	
	//cout<<"(cargar_palabras) - fin\n";
	
	return numero_palabras;
}

inline void iniciar_lectores(int numero_palabras, char **palabras, int id_grupo, const char *ruta_listas, LectorLista ** &lectores){

	//cout<<"(iniciar_lectores) - inicio\n";
	
	lectores=new LectorLista*[numero_palabras];
	char *linea=new char[255];
	for(int i=0; i<numero_palabras; i++){
		if(strlen(palabras[i])){
			if(ruta_listas[strlen(ruta_listas)-1]=='/')
				sprintf(linea, "%slista_texto_%i_%c.txt", ruta_listas, id_grupo, palabras[i][0]);
			else
				sprintf(linea, "%s/lista_texto_%i_%c.txt", ruta_listas, id_grupo, palabras[i][0]);
			lectores[i]=new LectorLista(linea);
		}
		else{
			lectores[i]=NULL;
		}
	}
	delete linea;
	
	//cout<<"(iniciar_lectores) - fin\n";
}

inline void cargar_listas(int numero_palabras, char **palabras, LectorLista **lectores, list<int> ** &listas){
	
	//cout<<"(cargar_listas) - inicio\n";
	
	listas=new list<int>*[numero_palabras];
	for(int i=0; i<numero_palabras; i++){
		if(lectores[i]!=NULL){
			listas[i]=new list<int>();
		}
		else{
			listas[i]=NULL;
		}
	}
	
	char *linea=new char[255];
	
	bool encontrada;
	int numero;
	int lectura;
	
	for(int i=0; i<numero_palabras; i++){
	
		if(lectores[i]!=NULL){
			
			//cout<<"palabra: \""<<palabras[i]<<"\"\n";
			
			//lectores[i]
			//listas[i]
			encontrada=false;
	
			while(lectores[i]->estado!=EOF){
				lectura=lectores[i]->palabra(linea);
				if(strcmp(linea, palabras[i])==0){
					//leer y cargar lista
					while(true){
						lectura=lectores[i]->palabra(linea);
						//cout<<"string: \""<<palabra<<"\"\n";
						numero=atoi(linea);
						//cout<<"numero: \""<<numero<<"\"\n";
						//Agregar a la lista
						if(numero){
							listas[i]->push_back(numero);
						}
						if(lectores[i]->estado==1){
							//fin de linea, nueva palabra
							break;
						}
					}
					//cout<<"Numero de Resultados: "<<listas[i]->size()<<"\n";
					encontrada=true;
					break;
				}
				else{
					lectores[i]->saltar_linea();
				}
		
			}
			if(!encontrada){
				//cout<<"Palabra No Encontrada\n";
			}
			
		}//if... su lector es valido
	}//for... cada palabra
	
	delete [] linea;
	
	//cout<<"(cargar_listas) - fin\n";
}

inline void intersectar_listas(int numero_palabras, list<int> **listas, list<int> * &lista_final){

	//cout<<"(intersectar_listas) - inicio\n";
	
	lista_final=new list<int>();
	if(numero_palabras==0){
		return;
	}
	else if(numero_palabras==1){
		list<int>::iterator it;
		for(it=(listas[0])->begin(); it!=(listas[0])->end(); it++){
			lista_final->push_back(*it);
		}
		return;
	}
	else{
		//Hay al menos dos listas...
		
		//Se toma la primera lista como eliminador
		//se mantiene la posicion de busqueda en cada una de las otras
		list<int>::iterator *it=new list<int>::iterator[numero_palabras-1];
		for(int i=0; i<numero_palabras-1; i++){
			if(listas[i+1]!=NULL){
				it[i]=listas[i+1]->begin();
			}
		}
		
		list<int>::iterator eliminador;
		int encontrado;
		int esperado=0;
		//Se omiten las listas nulas
		for(int i=0; i<numero_palabras-1; i++){
			if(listas[i+1]!=NULL){
				esperado++;
			}
		}
		
		for(eliminador=listas[0]->begin(); eliminador!=listas[0]->end(); eliminador++){
			//cout<<"eliminador: "<<(*eliminador)<<"\n";
			encontrado=0;
			for(int i=0; i<numero_palabras-1; i++){
				if(listas[i+1]!=NULL){
					for(;it[i]!=listas[i+1]->end(); (it[i])++){
						//cout<<"comparando con "<<*(it[i])<<"\n";
						if((*(it[i]))==(*eliminador)){
							encontrado++;
							(it[i])++;
							break;
						}
						else if((*(it[i]))>(*eliminador)){
							break;
						}
					}//for... busqueda en la lista i
				}
			}//for... cada lista adicional a la primera
			if(encontrado==esperado){
				//cout<<"Encontrado\n";
				lista_final->push_back(*eliminador);
			}
			else{
				//cout<<"No encontrado\n";
			}
			
		}
		
	}
	
	//cout<<"(intersectar_listas) - fin\n";
	
}

int main(int argc, char* argv[]){

	if(argc!=4){
		cout<<"Modo de uso\n>numero_resultados consulta id_grupo ruta_listas\n";
		return 0;
	}
	
	const char *ruta_listas=argv[3];
	
	//clock_t inicio, fin;
	//double tiempo_iniciar, tiempo_cargar, tiempo_intersectar;
	
	int numero_palabras;
	char **palabras=NULL;
	LectorLista **lectores=NULL;
	list<int> **listas=NULL;
	list<int> *lista_final=NULL;
	
	char *linea=new char[255];
	int id_grupo=atoi(argv[2]);
	
	numero_palabras=cargar_palabras(argv[1], palabras);
	//cout<<"de regreso en main... ("<<numero_palabras<<" palabras)\n";
	if(numero_palabras==0){
		return 0;
	}
	
	iniciar_lectores(numero_palabras, palabras, id_grupo, ruta_listas, lectores);
	
	cargar_listas(numero_palabras, palabras, lectores, listas);
	
	intersectar_listas(numero_palabras, listas, lista_final);
	
	int resultado=lista_final->size();
	
	cout<<resultado<<"\n";
	
	/*
	list<int>::iterator it;
	cout<<lista_final->size()<<" elementos (";
	for(it=lista_final->begin(); it!=lista_final->end(); it++){
		//cout<<(*it)<<" ";
	}
	cout<<")\n";
	*/
	
	if(palabras!=NULL){
		for(int i=0; i<numero_palabras; i++){
			if(palabras[i]!=NULL){
				delete palabras[i];
			}
		}
		delete [] palabras;
	
	}
		
	if(lectores!=NULL){
		for(int i=0; i<numero_palabras; i++){
			if(lectores[i]!=NULL){
				delete lectores[i];
			}
		}
		delete [] lectores;
	}
	
	if(listas!=NULL){
		for(int i=0; i<numero_palabras; i++){
			if(listas[i]!=NULL){
				delete listas[i];
			}
		}
		delete [] listas;
	}
	
	if(lista_final!=NULL)
		delete lista_final;
		
	delete [] linea;
	
	return resultado;
}
