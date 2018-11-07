parse_xml <- function(FileName) {		
  t_doc <- xmlParse(FileName)
  doc <- xmlToDataFrame(nodes=getNodeSet(t_doc,"//DOC"))[c("DOCNO","TITLE","AUTHOR","TEXT","LABLE")]
}

mgsub <- function(pattern, replacement, x, ...) {
  if (length(pattern)!=length(replacement)) {
    stop("pattern and replacement do not have the same length.")
  }
  result <- x
  for (i in 1:length(pattern)) {
    result <- gsub(pattern[i], replacement[i], result, ...)
  }
  result
}