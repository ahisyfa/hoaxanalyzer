tdm.generate <- function(string, ng){
  
  #list_stopwords <- as.list(read.csv("stopwords.csv", header = FALSE))
  corpus <- Corpus(VectorSource(string))
  corpus <- tm_map(corpus, content_transformer(tolower))
  corpus <- tm_map(corpus, removeNumbers) 
  corpus <- tm_map(corpus, removePunctuation)
  corpus <- tm_map(corpus, stripWhitespace)
  #corpus <- tm_map(corpus, removeWords, list_stopwords$V1)
  options(mc.cores=1)
  NTokenizer <- function(x) NGramTokenizer(x, Weka_control(min = ng, max = ng))
  tdm <- TermDocumentMatrix(corpus, control = list(tokenize = NTokenizer))
  tdm
}

tdm.generate_with_delete_stopword <- function(string, ng){
  
  list_stopwords <- as.list(read.csv("stopwords.csv", header = FALSE))
  corpus <- Corpus(VectorSource(string))
  corpus <- tm_map(corpus, content_transformer(tolower))
  corpus <- tm_map(corpus, removeNumbers) 
  corpus <- tm_map(corpus, removePunctuation)
  corpus <- tm_map(corpus, stripWhitespace)
  corpus <- tm_map(corpus, removeWords, list_stopwords$V1)
  options(mc.cores=1)
  NTokenizer <- function(x) NGramTokenizer(x, Weka_control(min = ng, max = ng))
  tdm <- TermDocumentMatrix(corpus, control = list(tokenize = NTokenizer))
  tdm
}