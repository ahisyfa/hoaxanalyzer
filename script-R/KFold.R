generateFolds <- function(mydb){
  folds <- createFolds(data$LABLE, k = 2, list = TRUE, returnTrain = FALSE)
  # untuk data percobaan yang hanya sedikit (n=10) maka k = 2, 
  # karena jika k = 10 maka data uji = 1 -> n/k = 10/10 = 1
  
  for(i in 1:length(folds)){
    documents <- as.String(data$DOCNO[folds[[i]][1]])
    
    for(j in 2:length(folds[[i]])){
      documents <- paste(documents, as.String(data$DOCNO[folds[[i]][j]]), sep = ",")
    }
    
    query <- "insert into `k_folds` (`documents`) values ('param_documents')"
    query <- gsub("\\<param_documents\\>", documents, query)
    rs = dbSendQuery(mydb, query)
  }

}