
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

#include "Lector.h"
#include "LectorLista.h"

using namespace std;

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

//Dice si el caracter es una letra estandar
inline bool es_letra(int c){
	if(c>='A' && c<='Z'
	|| c>='a' && c<='z'
	|| c>='0' && c<='9'){
		return true;
	}
	return false;
}

//retorna el numero de caracteres que no son estandar
inline int caracteres_incorrectos(char *s){
	int n=0;
	for(int i=0; i<strlen(s); i++){
		if(!es_letra(s[i]))
			n++;
	}
	return n;
}

inline bool eliminar_rapido(char *s){
	for(int i=0; i<strlen(s); i++){
		if(!es_letra(s[i]))
			return true;
	}
	return false;
}

inline void ordenar_listas(map<string, list<int> > *lista){
	map<string, list<int> >::iterator it_lista;
	for(it_lista=lista->begin(); it_lista!=lista->end(); it_lista++){
		((*it_lista).second).sort();
		((*it_lista).second).unique();
	}
}

inline void escribir_resultado(const char *ruta_destino, int id_grupo, char letra, 
	bool binario, map<string, list<int> > *lista){
	
	int n;
	fstream *escritor;
	char *linea=new char[255];
	
	map<string, list<int> >::iterator it_lista;
	list<int>::iterator it_i;
	
	if(binario){
		
		//El formato es:
		//[int numero_palabras][int largo_palabra][char* palabra][int largo_lista][int l1][int l2]...[int ln][...]
		if(ruta_destino[strlen(ruta_destino)-1]=='/')
			sprintf(linea, "%slista_binaria_%i_%c", ruta_destino, id_grupo, letra);
		else
			sprintf(linea, "%s/lista_binaria_%i_%c", ruta_destino, id_grupo, letra);
		cout<<"[Binario] - Escribiendo en "<<linea<<"\n";
		escritor=new fstream(linea, fstream::trunc | fstream::binary | fstream::out);
		
		n=lista->size();
		//son n palabras
		escritor->write((char*)(&n), sizeof(int)/sizeof(char));
		
		for(it_lista=lista->begin(); it_lista!=lista->end(); it_lista++){
			//cout<<(*it_lista).first<<"(";
			n=((*it_lista).first).size();
			escritor->write((char*)(&n), sizeof(int)/sizeof(char));
			escritor->write(((*it_lista).first).data(), n);
			n=((*it_lista).second).size();
			escritor->write((char*)(&n), sizeof(int)/sizeof(char));
			
			for(it_i=((*it_lista).second).begin(); it_i!=((*it_lista).second).end(); it_i++){
				//cout<<" "<<*it_i<<" ";
				n=(*it_i);
				escritor->write((char*)(&n), sizeof(int)/sizeof(char));
			}
			//cout<<")\n";
		}
		
	}
	else{
		
		if(ruta_destino[strlen(ruta_destino)-1]=='/')
			sprintf(linea, "%slista_texto_%i_%c.txt", ruta_destino, id_grupo, letra);
		else
			sprintf(linea, "%s/lista_texto_%i_%c.txt", ruta_destino, id_grupo, letra);
		cout<<"[Texto] - Escribiendo en "<<linea<<"\n";
		escritor=new fstream(linea, fstream::trunc | fstream::out);
		
		for(it_lista=lista->begin(); it_lista!=lista->end(); it_lista++){
			//cout<<(*it_lista).first<<"(";
			escritor->write(((*it_lista).first).data(), ((*it_lista).first).size());
			for(it_i=((*it_lista).second).begin(); it_i!=((*it_lista).second).end(); it_i++){
				//cout<<" "<<*it_i<<" ";
				sprintf(linea, " %i", *it_i);
				escritor->write(linea, strlen(linea));
			}
			sprintf(linea, " \n");
			escritor->write(linea, strlen(linea));
		}
		
	}
	
	escritor->close();
	delete escritor;
	delete [] linea;

}

int main(int argc, char* argv[]){
	
	if(argc!=5){
		cout<<"Modo de uso\n>separador_lista ruta_origen ruta_destino id_grupo numero_archivos\n";
		return 0;
	}
	
	const char *ruta_origen=argv[1];
	const char *ruta_destino=argv[2];
	int id_grupo=atoi(argv[3]);
	int numero_archivos=atoi(argv[4]);
	
	char **archivos=new char*[numero_archivos];
	for(int i=0; i<numero_archivos; i++)
		archivos[i]=new char[255];
	if(numero_archivos==1){
		if(ruta_origen[strlen(ruta_origen)-1]=='/')
			sprintf(archivos[0], "%slista_texto_%i.txt", ruta_origen, id_grupo);
		else
			sprintf(archivos[0], "%s/lista_texto_%i.txt", ruta_origen, id_grupo);
	}
	else{
		if(ruta_origen[strlen(ruta_origen)-1]=='/'){
			for(int i=0; i<numero_archivos; i++)
				sprintf(archivos[i], "%slista_texto_%i_%i.txt", ruta_origen, id_grupo, (1+i));
		}
		else{
			for(int i=0; i<numero_archivos; i++)
				sprintf(archivos[i], "%s/lista_texto_%i_%i.txt", ruta_origen, id_grupo, (1+i));
		}
	}
	char letra;
	
	bool binario=false;
	
	cout<<"Inicio (";
	if(binario)
		cout<<"binario";
	else
		cout<<"texto";
	cout<<")\n";
	
	clock_t inicio, fin;
	inicio=clock();
	
	char *palabra=new char[255];
	string *s;
	
	map<string, list<int> > *lista;
	
	LectorLista *lector=new LectorLista("");
	
	int lectura;
	int numero;
	
	bool lectura_iniciada;
	
	for(letra='a'; letra<='z'; letra++){
		cout<<"Iniciando Letra \""<<letra<<"\"\n";
		
		lista=new map<string, list<int> >();
		
		//Fijar un archivo
		for(int k=0; k<numero_archivos; k++){
			
			lectura_iniciada=false;
			lector->reiniciar(archivos[k]);
			//cout<<"Estado del lector \""<<archivos[k]<<"\": "<<lector->estado<<"\n";
			if(lector->estado!=EOF){
				
				cout<<"Leyendo "<<archivos[k]<<"...\n";
				
				while(lector->estado!=EOF){
					//Primero se lee la palabra
					lectura=lector->palabra(palabra);
					//cout<<"nueva palabra: \""<<palabra<<"\"\n";
					
					//Si la palabra empieza por la letra en cuestion...
					if(palabra[0]==letra){
						lectura_iniciada=true;
						s=new string(palabra);
						//Se lee cada numero, y se agrega a la lista como entero
						while(true){
							lectura=lector->palabra(palabra);
							//cout<<"string: \""<<palabra<<"\"\n";
							numero=atoi(palabra);
							//cout<<"numero: \""<<numero<<"\"\n";
							//Agregar a la lista
							if(numero){
								((*lista)[*s]).push_back(numero);
							}
							if(lector->estado==1){
								//fin de linea, nueva palabra
								break;
							}
							//break;
						}
						delete s;
					}
					else if(lectura_iniciada){
						//ya se inicio, se leyeron palabras con la letra, pero ya se leyo una que no corresponde
						cout<<"palabra de termino: \""<<palabra<<"\"\n";
						break;
					}
					else{
						//Para acelerar, se avanza una linea
						lector->saltar_linea();
					}
				}
		
			}//if... el lector es bueno
		
		}//for... cada archivo
		
		ordenar_listas(lista);
		
		//Escribir resultado
		escribir_resultado(ruta_destino, id_grupo, letra, binario, lista);
		
		delete lista;
		
	}//for... cada letra
	
	//Los numeros
	for(letra='0'; letra<='9'; letra++){
		cout<<"Iniciando Letra \""<<letra<<"\"\n";
		
		lista=new map<string, list<int> >();
		
		//Fijar un archivo
		for(int k=0; k<numero_archivos; k++){
			
			lectura_iniciada=false;
			lector->reiniciar(archivos[k]);
			//cout<<"Estado del lector \""<<archivos[k]<<"\": "<<lector->estado<<"\n";
			if(lector->estado!=EOF){
				
				cout<<"Leyendo "<<archivos[k]<<"...\n";
				
				while(lector->estado!=EOF){
					//Primero se lee la palabra
					lectura=lector->palabra(palabra);
					//cout<<"nueva palabra: \""<<palabra<<"\"\n";
					
					//Si la palabra empieza por la letra en cuestion...
					if(palabra[0]==letra){
						lectura_iniciada=true;
						s=new string(palabra);
						//Se lee cada numero, y se agrega a la lista como entero
						while(true){
							lectura=lector->palabra(palabra);
							//cout<<"string: \""<<palabra<<"\"\n";
							numero=atoi(palabra);
							//cout<<"numero: \""<<numero<<"\"\n";
							//Agregar a la lista
							if(numero){
								((*lista)[*s]).push_back(numero);
							}
							if(lector->estado==1){
								//fin de linea, nueva palabra
								break;
							}
							//break;
						}
						delete s;
					}
					else if(lectura_iniciada){
						//ya se inicio, se leyeron palabras con la letra, pero ya se leyo una que no corresponde
						cout<<"palabra de termino: \""<<palabra<<"\"\n";
						break;
					}
					else{
						//Para acelerar, se avanza una linea
						lector->saltar_linea();
					}
				}
		
			}//if... el lector es bueno
		
		}//for... cada archivo
		
		ordenar_listas(lista);
		
		//Escribir resultado
		escribir_resultado(ruta_destino, id_grupo, letra, binario, lista);
		
		delete lista;
		
	}//for... cada letra
	
	delete lector;
	
	//delete linea;
	
	cout<<"Terminando...\n";
	
	fin=clock();
	cout<<"Tiempo: "<<((double)fin-inicio)/CLOCKS_PER_SEC<<"\n";
	
	delete [] archivos;
	delete [] palabra;
	
	
	cout<<"Fin\n";
	
}



