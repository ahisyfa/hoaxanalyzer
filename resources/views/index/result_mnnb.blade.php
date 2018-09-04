<div class="row" style="text-align: left">

    <div class="col-md-12" style="text-align: center">
        <h3>Result of Multinomial Naive Bayes Classification :</h3>
    </div>

    <div class="col-md-12" style="text-align: center; padding-top: 20px;">
        <div class="progress" style="height: 30px;">
            <div class="progress-bar progress-bar-danger" style="width: {{ round($persen_peluang_hoax, 2) }}%; font-size: 16pt; line-height: 30px;">
                {{ round($persen_peluang_hoax, 2) }}% HOAX
            </div>
            <div class="progress-bar progress-bar-success" style="width: {{ round($persen_peluang_non_hoax, 2) }}%; font-size: 16pt; line-height: 30px;">
                {{ round($persen_peluang_non_hoax, 2) }}% BUKAN HOAX
            </div>
        </div>
    </div>

</div>