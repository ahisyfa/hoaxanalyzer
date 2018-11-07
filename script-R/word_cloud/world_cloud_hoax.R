options(warn=1)

# Menghitung frequensi per kata
text = readLines("D:\\skripsi\\script-R\\word_cloud\\terms_hoax.txt")
text = unlist(strsplit(text, "[| ]+"))
text = tolower(text)
text = data.frame(table(text))

# Generate word-cloud
wordcloud(text$text, text$Freq, max.words=50, random.order=FALSE, rot.per=0.25, colors=brewer.pal(8, "Dark2"))

