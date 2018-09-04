@extends('layouts.layout')

@section('content')

	
<div id="home" style="margin-top: 30px;">
	<div class="container">
		<div class="agileits_w3layouts_heding">
			<p>Hoax analyzer using</p>
			<h3>K-Nearest Neighbour <span>and</span> Multinomial Naive Bayes</h3>
			<p><img src="images/logoipb.png" width="25" height="25"/> Institut Pertanian Bogor</p>
			<img src="images/head1.png" alt="Lines" />
		</div>

		<div class="resp-tabs-container">
			<div class="row">
				<div class="reset">
					<form id="form_classification" action="#" method="post">
						{{ csrf_field() }}

						<div class="col-md-12 fields">
							<textarea name="teks" placeholder="Paste the suspicious news here! (the result can be better if your input is longer than one sentence)" required="" style="height: 170px!important;"></textarea>
						</div>

						<div class="clearfix"></div>
						<br>

						<div class="col-sm-push-3 col-md-6 fields center-agileits">
							<h6>Select the method</h6>
							<select name="metode" class="form-control">
								<option value="1">K-Nearest Neighbour</option>
								<option value="2">Multinomial Naive Bayes</option>
							</select>
						</div>

						<div class="clearfix"></div>

						<input type="submit" value="Analyze">
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_result" tabindex="-1" data-backdrop="static" role="dialog">
	<div class="modal-dialog">

		<div id="result" class="col-md-12 agileits_schedule_bottom_right">

			<div class="w3ls_schedule_bottom_right_grid">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3><span>Result</span></h3>
				</br>
				<div class="row" id="result_classification"></div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>


<div class="agile_services">
	<div class="col-md-6 agileinfo_schedule_bottom_left">
		<img src="images/schedule.jpg" alt=" " class="img-responsive" />
	</div>
	<div class="col-md-6 agileits_schedule_bottom_right">
		<div class="w3ls_schedule_bottom_right_grid">
			<h3>About <span>Hoax</span></h3>
			<p>Hoaxes (the term origins from: hocus to trick) is misleading information which can destroy someone credibility and reputation. Usual intention of hoax creator is to persuade or manipulate other people to do or prevent pre-established actions, mostly by using a threat or deception. The spreading of hoax news can be an obstacle for human growth.
			Therefore people must clarify the truth of the news before spreading it, so it can’t harm the others.
			</p>
			<div class="clearfix"> </div>
		</div>
	</div>
	<div class="clearfix"> </div>
</div>


<div class="features">
	<div class="container">
		<div class="agileits_w3layouts_heding">
			<!--<p>We provide best Solutions</p>-->
			<h3>How it <span>Works</span></h3>
			<img src="images/head1.png" alt="Lines"/>
		</div>
		<div class="col-md-12">
			<img src="images/tahapan.png" alt=" " class="img-responsive center-block"/>
		</div>
		<div class="clearfix"></div>
		<div class="col-md-4">
		</div>

	</div>
</div>

<div id="team" class="team">
	<div class="container">
	<div class="agileits_w3layouts_heding">
			<p class="white-w3ls">Hoax Analyzer</p>
			<h3 class="white-w3ls">Meet our <span>Team</span></h3>
			<img src="images/head1.png" alt="Lines" />
		</div>
		<div class="col-md-1">
		</div>
		<div class="w3_agile_team_grids">
			<div class="col-md-2 w3_agile_team_grid">
				<div class="hover14 column">
					<figure><img src="images/5.jpg" alt=" " class="img-responsive" /></figure>
				</div>
				<h3>Suhar Prasetyo</h3>
				<p>G64154005</p>
				<div class="w3l-social">
					<ul>
						<li><a href="https://www.facebook.com/suhar.tyo"><i class="fa fa-facebook"></i></a></li>
						<li><a href="#"><i class="fa fa-twitter"></i></a></li>
						<li><a href="#"><i class="fa fa-google-plus"></i></a></li>
					</ul>
				</div>
			</div>
			<div class="col-md-2 w3_agile_team_grid">
				<div class="hover14 column">
					<figure><img src="images/6.jpg" alt=" " class="img-responsive" /></figure>
				</div>
				<h3>Shaugi Chasbullah</h3>
				<p>G64154064</p>
				<div class="w3l-social">
					<ul>
						<li><a href="https://www.facebook.com/shaugi.nithue"><i class="fa fa-facebook"></i></a></li>
						<li><a href="https://twitter.com/skullker94"><i class="fa fa-twitter"></i></a></li>
						<li><a href="https://plus.google.com/u/1/108311147601195581070"><i class="fa fa-google-plus"></i></a></li>
					</ul>
				</div>
			</div>
			<div class="col-md-2 w3_agile_team_grid">
				<div class="hover14 column">
					<figure><img src="images/7.jpg" alt=" " class="img-responsive" /></figure>
				</div>
				<h3>Aulia Afriza</h3>
				<p>G64154054</p>
				<div class="w3l-social">
					<ul>
						<li><a href="https://www.facebook.com/aulia.potter"><i class="fa fa-facebook"></i></a></li>
						<li><a href="#"><i class="fa fa-twitter"></i></a></li>
						<li><a href="#"><i class="fa fa-google-plus"></i></a></li>
					</ul>
				</div>
			</div>
			<div class="col-md-2 w3_agile_team_grid">
				<div class="hover14 column">
					<figure><img src="images/8.jpg" alt=" " class="img-responsive" /></figure>
				</div>
				<h3>Kania Latansa</h3>
				<p>G64154043</p>
				<div class="w3l-social">
					<ul>
						<li><a href="https://www.facebook.com/kania.latansaarziahutagaol"><i class="fa fa-facebook"></i></a></li>
						<li><a href="#"><i class="fa fa-twitter"></i></a></li>
						<li><a href="#"><i class="fa fa-google-plus"></i></a></li>
					</ul>
				</div>
			</div>
			<div class="col-md-2 w3_agile_team_grid">
				<div class="hover14 column">
					<figure><img src="images/9.jpg" alt=" " class="img-responsive" /></figure>
				</div>
				<h3>Ahmad Isyfalana Amin</h3>
				<p>G64154033</p>
				<div class="w3l-social">
					<ul>
						<li><a href="https://www.facebook.com/ahmadaminalf"><i class="fa fa-facebook"></i></a></li>
						<li><a href="#"><i class="fa fa-twitter"></i></a></li>
						<li><a href="#"><i class="fa fa-google-plus"></i></a></li>
					</ul>
				</div>
			</div>
			<div class="clearfix"> </div>
		</div>
	</div>
</div>

<div class="w3layouts_newsletter">
	<div class="container">
		<div class="agileits_w3layouts_heding">
			<!--<p>Get Updates</p>-->
			<h3><span>Thank </span> You</h3>
			<img src="images/head1.png" alt="Lines" />
		</div>
		<div class="w3layouts_newsletter_right">
		<p class="para-w3layouts white"> We'd like to thanks <font color="1bbde8"><b>Ir Julio Adisantoso, MKom</b></font> as our supervisor who has guided us and gave many suggestions in this research. Please be advised that this application is still under development and we cannot guarantee the accuracy of the results you may encounter. </p>
		<p class="para-w3layouts white"> - Prasetyo et al. 2017 - </p>
		</div>
		<div class="clearfix"> </div>
	</div>
</div>

<div class="w3_agile_footer">
	<div class="container">
		<p>© <?php echo date('Y') ?> Hoax Analyzer IPB. All rights reserved | Design by <a href="http://w3layouts.com">W3layouts.</a></p>
		<div class="arrow-container animated fadeInDown">
			<a href="#home" class="arrow-2 scroll">
				<i class="fa fa-angle-up"></i>
			</a>
			<div class="arrow-1 animated hinge infinite zoomIn"></div>
		</div>
	</div>
</div>


<script src="js/bootstrap.js"></script>
<script src="js/jquery-ui.js"></script>
<script src="js/responsiveslides.min.js"></script>
<script src="js/easy-responsive-tabs.js"></script>
<script src="js/jquery.flexslider.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.countup.js"></script>


<script type="text/javascript">
	$(window).load(function(){
		$('.flexslider').flexslider({
			animation: "slide",	start: function(slider){
				$('body').removeClass('loading');
			}
		});

        $("#slider-range").slider({
            range: true,
            min: 0,
            max: 9000,
            values: [ 1000, 7000 ],
            slide: function( event, ui ) {
                $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            }
        });
        $( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) + " - $" + $( "#slider-range" ).slider( "values", 1 ) );


    });

    jQuery(document).ready(function($) {
        $(".scroll").click(function(event){
            event.preventDefault();
            $('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
        });

        $('#form_classification').submit(function(e){
            e.preventDefault();

            $('#result_classification').html('<img src="images/ajax-loader.gif" alt=" "/>');
            $('#modal_result').modal('show');

            $.ajax({
                url: 'klasifikasi/submit',
                method: 'POST',
                type: 'html',
                data: $('#form_classification').serialize(),
                success: function(datanya){
                    $('#result_classification').html(datanya);
                    console.log(datanya);

                }
            });
        });

    });

</script>


@endsection