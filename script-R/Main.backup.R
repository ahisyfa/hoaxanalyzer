#require(devtools)
# install_version("tm", version = "0.6-2", repos = "http://cran.us.r-project.org")

library("tm")
library("RMySQL")
library("RWeka")
library("XML")
library("plyr")
library("caret")
library("wordcloud")
library("RColorBrewer")

# setwd("D:/Skripsi/R/scriptR ver9")
setwd("D:/skripsi/script-R")
source("Functions.R")
source("GenerateTDM.R")
source("KFold.R")

files <- list.files(path = "data_bersih", pattern = "xml$", full.names = TRUE)

data <- ldply(files,parse_xml)
data$TEXT <- gsub("^\\s+|\\s+$|\t", "", data$TEXT)
data$TITLE <- gsub("^\\s+|\\s+$|\t", "", data$TITLE)
data$KONTEN <- paste(data$TITLE, data$TEXT)

normalization <- read.csv("normalization.csv", header = FALSE)

normalization$V1 <- paste("\\<",normalization$V1)
normalization$V1 <- paste(normalization$V1,"\\>")
normalization$V1 <- gsub(" ", "", normalization$V1)

nonbaku <- as.character(normalization$V1)
baku <- as.character(normalization$V2)

data$KONTEN <- mgsub(nonbaku,baku,data$KONTEN)

#tdm <- tdm.generate(data[,6], 1)
tdm <- tdm.generate(data[,6], 1) #ganti 1 kalau mau unigram
tdm.matrix <- as.matrix(tdm)

#membuat wordcloud
v <- sort(rowSums(tdm.matrix),decreasing=TRUE)
d <- data.frame(word = names(v),freq=v)
head(d, 10)
set.seed(1234)
wordcloud(words = d$word, freq = d$freq, min.freq = 3, scale = c(1.5, 0.2),
          max.words=100, random.order=FALSE, rot.per=0.35, 
          colors=brewer.pal(8, "Dark2"))

#View(tdm.matrix)

# Pake MySQL
mydb <- dbConnect(MySQL(), user='root', password='', dbname='db_ulikan', host='localhost')

# generate folds
generateFolds(mydb)

# persiapan memasukkan tdm ke tabel tdms
matrixtdm <- tdm.matrix
rownames = row.names(matrixtdm)

#membuat csv
for(h in 1:600){
  tdm <- tdm.generate(data[h,6], 2) #ganti 1 kalau mau unigram
  tdm.matrix <- as.matrix(tdm)
  matrixtdm <- tdm.matrix
  rownames = row.names(matrixtdm)
  tdmbigram <- matrix(ncol = 5)
  colnames(tdmbigram) <- c("term", "frequency", "document", "class", "test_data")
  for(i in 1:nrow(matrixtdm)){
    for(j in h:h){
              isi <- c(rownames[i], matrixtdm[i,], as.String(data$DOCNO[j]), as.String(data$LABLE[j]), 0)
              tdmbigram <- rbind(tdmbigram,isi)
    }
  }
  tdmbigram <- tdmbigram[-1,]
  write.table(tdmbigram, file = "data_csv/1.csv", row.names = FALSE, col.names = FALSE, sep = ",", quote = FALSE)
  query = "LOAD DATA LOCAL INFILE 'RFILE' INTO TABLE RTABLE FIELDS TERMINATED by ',' LINES TERMINATED BY '\n'"
  query <- gsub("\\<RFILE\\>", 'data_csv/1.csv', query)
  query <- gsub("\\<RTABLE\\>", 'tdms', query)
  rs = dbSendQuery(mydb, query)
}

