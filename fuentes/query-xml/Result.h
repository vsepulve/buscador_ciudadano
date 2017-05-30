#if !defined(_RESULT_H)
#define _RESULT_H

#define MAX_URL_SIZE    255
#define MAX_TITLE_SIZE  100
#define MAX_SUMMARY_SIZE 240
#define MAX_META_SIZE 400

class Result{
public : 
	char *url;
	int url_length;
	char *title;
	int title_length;
	char *summary;
	int summary_length;
	char *meta;
	int meta_length;
	char type;
	int  base_id;
	int  size;
	int  doc_id;
	float sim;
	int rank;
	Result(){
		url=new char[MAX_URL_SIZE+1];
		title=new char[MAX_TITLE_SIZE+1];
		summary=new char[MAX_SUMMARY_SIZE+1];
		meta=new char[MAX_META_SIZE+1];
	}
	~Result(){
		//printf("~Result\n");
		delete [] url;
		delete [] title;
		delete [] summary;
		delete [] meta;
	}
};

#endif

