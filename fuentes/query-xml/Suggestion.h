#if !defined(_SUGGESTION_H)
#define _SUGGESTION_H

#define MAX_SUGGESTION_SIZE  255

class Suggestion{
public : 
	char *text;
	int text_length;
	Suggestion(){
		text=new char[MAX_SUGGESTION_SIZE+1];
	}
	~Suggestion(){
		//printf("~Suggestion\n");
		delete [] text;
	}
};

#endif

