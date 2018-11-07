#require(devtools)
# install_version("tm", version = "0.6-2", repos = "http://cran.us.r-project.org")

# Set Java Environment secara manual
# karena JRE tidak terdeteksi otomatis di laptop ini.
Sys.setenv(JAVA_HOME='C:\\Program Files (x86)\\Java\\jre1.8.0_181') 

# Load Library
library("tm")
library("RMySQL")
library("RWeka")
library("XML")
library("plyr")
library("caret")
library("wordcloud")
library("RColorBrewer")

# Set Working Directory
setwd("D:/skripsi/script-R")

# Gunakan fungsi dari script 
source("Functions.R")
source("GenerateTDM.R")
source("KFold.R")

# ===============================
# TAHAPAN PRAPROSES
# 1. Tokenisasi
# 2. Pembakuan kata
# 3. Penghapusan stopwords
# ===============================


# ---------------------------------------------
# Load data penelitian dari folder data_bersih
# ---------------------------------------------
# Load direktori data uji
files       <- list.files(path = "data_bersih", pattern = "xml$", full.names = TRUE)

# Ambil data XML
data        <- ldply(files,parse_xml)
data$TEXT   <- gsub("^\\s+|\\s+$|\t", "", data$TEXT)
data$TITLE  <- gsub("^\\s+|\\s+$|\t", "", data$TITLE)
data$KONTEN <- paste(data$TITLE, data$TEXT)

# ------------------------------------
# PROSES TOKENISASI
# ------------------------------------
tdm        <- tdm.generate(data[,6], 1) # 1 artinya unigram
tdm.matrix <- as.matrix(tdm)


# Peringkat term tertinggi setelah tokenisasi
peringkat_term_setelah_tokenisasi     = data.frame(sort(rowSums(as.matrix(tdm)), decreasing=TRUE))
colnames(peringkat_term_setelah_tokenisasi) <- c("frekuensi")


# ------------------------------------
# PROSES NORMASLISASI (PEMBAKUAN KATA)
# ------------------------------------
# Ambil kamus normalisasi
normalization    <- read.csv("normalization.csv", header = FALSE)
normalization$V1 <- paste("\\<",normalization$V1)
normalization$V1 <- paste(normalization$V1,"\\>")
normalization$V1 <- gsub(" ", "", normalization$V1)
# Kamus kata aku dan tidak baku
nonbaku          <- as.character(normalization$V1)
baku             <- as.character(normalization$V2)
# Penghapusan stopwords
data$KONTEN      <- mgsub(nonbaku,baku,data$KONTEN)


# membuat tdm dengan data yang sudah dilakukan pembakuan kata
tdm  <- tdm.generate(data[,6], 1) # 1 artinya unigram

# Peringkat term tertinggi setelah pembakuan kata
peringkat_term_setelah_pembakuan_kata = data.frame(sort(rowSums(as.matrix(tdm)), decreasing=TRUE))
colnames(peringkat_term_setelah_pembakuan_kata) <- c("frekuensi")


# ------------------------------------
# PROSES PENGHAPUSAN STOPWORDS
# ------------------------------------
# Membuat TDM final
# Membuat term document matrix dengan data yang sudah 
# melalu proses pembakuan kata dan penghpausan stopwords.
tdm  <- tdm.generate_with_delete_stopword(data[,6], 1)# 1 artinya unigram
tdm.matrix <- as.matrix(tdm)

# Peringkat term tertinggi setelah penghapusan stopword
peringkat_term_setelah_hapus_stopwors = data.frame(sort(rowSums(as.matrix(tdm)), decreasing=TRUE))
colnames(peringkat_term_setelah_hapus_stopwors) <- c("frekuensi")


# View(tdm.matrix)



# -----------------------------------------
# AKHIR SCRIPT Main.R
#------------------------------------------