<div class="row" style="text-align: left">
    <div class="col-md-12" style="text-align: center">
        <h3>Result of KNN Classification :</h3>
    </div>

    <div class="col-md-12" style="text-align: center; padding-top: 20px;">
        <div class="progress" style="height: 30px">
            <div class="progress-bar progress-bar-danger" style="width: {{round($persen_hoax, 2)}}%; font-size: 16pt; line-height: 30px;">
                {{round($persen_hoax, 2)}}% HOAX
            </div>
            <div class="progress-bar progress-bar-success" style="width: {{round($persen_non_hoax, 2)}}%; font-size: 16pt; line-height: 30px;">
                {{round($persen_non_hoax, 2)}}% BUKAN HOAX
            </div>
        </div>
    </div>


    <div class="col-md-12">
        <strong>Detail : </strong>
        <ul style="padding: 6px 22px;">
            <li>Hasil klasifikasi : {{$KELAS_PREDIKSI}}</li>
            <li>Jumlah dokumen uji : {{$N}}</li>
            <li>Tetangga Hoax : {{$vote_hoax}}</li>
            <li>Tetangga Non-Hoax : {{$vote_non_hoax}}</li>
            <li>Bobot Hoax : {{$bobot_hoax}}</li>
            <li>Bobot Non-Hoax : {{$bobot_non_hoax}}</li>
        </ul>
    </div>
</div>