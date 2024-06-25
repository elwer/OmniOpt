<?php
	include("_header_base.php");
?>
	<link href="tutorial.css" rel="stylesheet" />
	<link href="jquery-ui.css" rel="stylesheet">
	<link href="prism.css" rel="stylesheet" />

	<h1>Plot your results</h1>
    
	<div id="toc"></div>

	There are many plots available and multiple options to show them. Here's a brief overview.

	<h2 id="plot-over-x11">Plot over X11</h2>
	<h3 id="plot-overview">Plot from overview</h3>

	To plot over X11, make sure you are connected with <tt>ssh -X user@login2.barnard.hpc.tu-dresden.de</tt> (of course, use the HPC system you wish instead of barnard, if applicable, and change it to your user).

	Then, <tt>cd</tt> into your OmniOpt2 directory. Assuming you have already ran an OmniOpt-run and the results are in <tt>runs/my_experiment/0</tt>, run this:

	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0</code></pre>

	You will be presented by a menu like this:<br>

	<img src="imgs/plot_overview.png" /><br>

	Use your arrow keys to navigate to the plot type you like, and then press enter.

	<h3 id="plot-overview">Plot directly</h3>
	If you know what plot you want, you can directly plot it by using:
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=scatter # change plot_type accordingly</code></pre>

	<h3 id="plot_to_file">Plot to file</h3>
	All, except the 3d scatter, support to export your plot to a file.
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=scatter --save_to_file filename.svg # change plot_type and file name accordingly. Allowed are svg and png.</code></pre>

	<h2 id="plot-types">Plot types</h2>
	<p>There are many different plot types, some of which can only be shown on jobs that ran on Taurus, or jobs with more than a specific number of results or parameters. If you run the <tt>omniopt_plot</tt>-script, it will automatically show you plots that are readily available.</p>

	<h3 id="trial_index_result">Plot trial index/result</h3>
	<img src="imgs/trial_index_result.png" /><br>
	TODO

	<h3 id="time_and_exit_code">Plot time and exit code infos</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=time_and_exit_code</code></pre>
	<img src="imgs/time_and_exit_code.png" /><br>
	TODO

	<h3 id="scatter">Scatter</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=scatter</code></pre>
	<img src="imgs/scatter.png" /><br>
	<p>The scatter plot shows you all 2d combinations of the hyperparameter space and, for each evaluation, a dot is printed. The color of the dot depends on the result value of this specific run. The lower, the greener, and the higher, the more red they are. Thus, you can see how many results were attained and how they were, and where they have been searched.</p>

	<h3 id="hex_scatter">Hex-Scatter</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=scatter_hex</code></pre>
	<img src="imgs/scatter_hex.png" /><br>

	<p>Similiar to scatter plot, but here many runs are grouped into hexagonal subspaces of the parameter combinations, and the groups are coloured by their average result, and as such you can see an approximation of the function space. This allows you to quickly grasp 'good' areas of your hyperparameter space.</p>

	<h3 id="scatter_generation_method">Scatter-Generation-Method</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=scatter_generation_method</code></pre>
	<img src="imgs/scatter_generation_method.png" /><br>
	TODO

	<h3 id="kde">KDE</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=kde</code></pre>
	<img src="imgs/kde.png" /><br>

	<p>Kernel-Density-Estimation-Plots, short <i>KDE</i>-Plots, group different runs into so-called bins by their result range and parameter range.</p>

	<p>Each grouped result gets a color, green means lower, red means higher, and is plotted as overlaying bar charts.</p>

	<p>These graphs thus show you, which parameter range yields which results, and how many of them have been tried, and how 'good' they were, i.e. closer to the minimum (green).</p>

	<h3 id="get_next_trials">get_next_trials got/requested</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=get_next_trials</code></pre>
	<img src="imgs/get_next_trials.png" /><br>
	TODO

	<h3 id="general">General job infos</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=general</code></pre>
	<img src="imgs/general.png" /><br>
	<p>The <tt>general</tt>-plot shows you general info about your job. It consists of four subgraphs:</p>

	<ul>
		<li><i>Results by Generation Method</i>: This shows the different generation methods, SOBOL meaning random step, and BoTorch being the model that is executed after the first random steps. The <i>y</i>-value is the Result. Most values are inside the blue box, little dots outside are considered outliars. Usually, you can see that the nonrandom model has far better results than the first random evaluations.</li>
		<li><i>Distribution of job status</i>: How many jobs were run and in which status they were. Different status include:</li>
		<ul>
			<li><i>COMPLETED</i>: That means the job has completed and has a result</li>
			<li><i>ABANDONED</i>: That means the job has been started, but, for example, due to timeout errors, the job was not able to finish with results</li>
			<li><i>MANUAL</i>: That means the job has been imported from a previous run</li>
			<li><i>FAILED</i>: That means the job has started but it failed and gained no result</li>
		</ul>
		<li><i>Correlation Matrix</i>: Shows you how each of the parameters correlates with each other and the final result. The higher the values, the more likely there's a correlation</li>
		<li><i>Distribution of Results by Generation Method</i>: This puts different results into so-called bins, i.e. groups of results in a certain range, and plots colored bar charts that tell you where how many results have been found by which method.</li>
	</ul>

	<h3 id="3d">3d</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=3d</code></pre>
	TODO

	<h3 id="gpu_usage">GPU usage</h3>
	<pre><code class="language-bash">./omniopt_plot --run_dir runs/my_experiment/0 --plot_type=gpu_usage</code></pre>
	TODO

	<script src="prism.js"></script>
	<script>
		Prism.highlightAll();
	</script>
	<script src="footer.js"></script>
</body>
</html>

