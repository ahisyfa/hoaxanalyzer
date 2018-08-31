<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TfIdf extends BaseModel
{
    
	public static function generate()
	{
        echo "TfIdf::generate() \r\n";

		TfIdf::truncate();
		
		$dfs = Df::where('feature_selection', true)->get();

		foreach ($dfs as $df) {

			$tdms = Tdm::where('test_data', false)->where('term', $df->term)->get();
			
			foreach ($tdms as $tdm) {
				$tfidf = new TfIdf();
				$tfidf->term = $tdm->term;
				$tfidf->tf_idf = $df->idf * $tdm->frequency;
				$tfidf->document = $tdm->document;
				$tfidf->class = $tdm->class;
				$tfidf->save();

                echo "TfIdf::generate() -> memproses term : {$tdm->term}, dokumen {$tdm->document}\r\n";
			}

		}

		return true;
	}

	public static function exportToCSV()
	{
		$csv = fopen(base_path() . '/tfidf/' . microtime(true) .'.csv', "w");
		$tf_idfs = TfIdf::orderBy('document', 'asc')->orderBy('term', 'asc')->get();
		$data = [];
		$terms = ['DOCNO'];
		foreach ($tf_idfs as $tf_idf) {
			if (!in_array($tf_idf->term, $terms)) {
				array_push($terms, $tf_idf->term);
			}
			if (!array_key_exists($tf_idf->document, $data)) {
				$data[$tf_idf->document] = ['class' => $tf_idf->class, 'value' => []];	
			}
			$temp = $data[$tf_idf->document]['value'];
			array_push($temp, $tf_idf->tf_idf);
			$data[$tf_idf->document]['value'] = $temp;
		}

		fputcsv($csv, array_merge($terms, ['class']));
		foreach ($data as $document => $tf_idf) {
			$row = array_merge([$document], $tf_idf['value'], [$tf_idf['class']]);
			fputcsv($csv, $row);
		}
		fclose($csv);

		return true;
	}

}
