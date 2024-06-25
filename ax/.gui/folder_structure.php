<?php
	include("_header_base.php");
?>
	<link href="tutorial.css" rel="stylesheet" />
	<link href="jquery-ui.css" rel="stylesheet">
	<link href="prism.css" rel="stylesheet" />

	<h1>Folder structure of OmniOpt runs</h1>
    
	<div id="toc"></div>

	<h2 id="runs_folder"><tt>runs</tt>-folder</h2>

	<p>For every experiment you do, there will be a new folder created inside the <tt>runs</tt>-folder in your OmniOpt2-installation.</p>

	<p>Each of these has a subfolder for each run that the experiment with that name was run. For example, if you run the experiment <tt>my_experiment</tt>
	twice, the paths <tt>runs/my_experiment/0</tt> and <tt>runs/my_experiment/1</tt> exist.

	<h3 id="runs_folder">Single files</h3>
	<pre><code class="language-bash">ls
best_result.txt  get_next_trials.csv  gpu_usage__i8033.csv  gpu_usage__i8037.csv  job_infos.csv  oo_errors.txt  parameters.txt  results.csv  single_runs  state_files  worker_usage.csv</code></pre>

	<h4 id="best_result"><tt>best_result.txt</tt></h4>

	<p>This file contains an ANSI-table that shows you the best result and the parameters resulted in that result.</p>

	<pre>
			      Best parameter:                              
┏━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━┳━━━━━━━━┳━━━━━━━━━━┓
┃ width_and_height ┃ validation_split ┃ learning_rate ┃ epochs ┃ result   ┃
┡━━━━━━━━━━━━━━━━━━╇━━━━━━━━━━━━━━━━━━╇━━━━━━━━━━━━━━━╇━━━━━━━━╇━━━━━━━━━━┩
│ 72               │ 0.184052         │ 0.001         │ 14     │ 1.612789 │
└──────────────────┴──────────────────┴───────────────┴────────┴──────────┘
</pre>
	
	<h4 id="get_next_trials"><tt>get_next_trials.csv</tt></h4>

	<p>A CSV file that contains the current time, the number of jobs <tt>ax_client.get_next_trials()</tt> got and the number it requested to get.</p>

	<pre>2024-06-25 08:55:46,1,20
2024-06-25 08:56:41,2,20
2024-06-25 08:57:14,5,20
2024-06-25 08:57:33,7,20
2024-06-25 08:59:54,15,20
...</pre>

	<h4 id="gpu_usage">GPU-usage-files</h4>

	<p>GPU usage files. They are the output of <tt>nvidia-smi</tt> and are periodically taken, when you run on a system with SLURM that allows you to connect to
	nodes that have running jobs on it with ssh.</tt>

	<p>Header line is omitted, but is: <tt>timestamp, name, pci.bus_id, driver_version, pstate, pcie.link.gen.max, pcie.link.gen.current, temperature.gpu, utilization.gpu [%], utilization.memory [%], memory.total [MiB], memory.free [MiB], memory.used [MiB]</tt>.</pre>

	<pre>2024/06/01 11:27:05.177, NVIDIA A100-SXM4-40GB, 00000000:3B:00.0, 545.23.08, P0, 4, 4, 44, 0 %, 0 %, 40960 MiB, 40333 MiB, 4 MiB
2024/06/01 11:27:05.188, NVIDIA A100-SXM4-40GB, 00000000:8B:00.0, 545.23.08, P0, 4, 4, 42, 0 %, 0 %, 40960 MiB, 40333 MiB, 4 MiB
2024/06/01 11:27:05.192, NVIDIA A100-SXM4-40GB, 00000000:0B:00.0, 545.23.08, P0, 4, 4, 43, 0 %, 0 %, 40960 MiB, 40333 MiB, 4 MiB
2024/06/01 11:27:15.309, NVIDIA A100-SXM4-40GB, 00000000:8B:00.0, 545.23.08, P0, 4, 4, 42, 3 %, 0 %, 40960 MiB, 1534 MiB, 38803 MiB
2024/06/01 11:27:15.311, NVIDIA A100-SXM4-40GB, 00000000:0B:00.0, 545.23.08, P0, 4, 4, 43, 3 %, 0 %, 40960 MiB, 1534 MiB, 38803 MiB
2024/06/01 11:27:15.311, NVIDIA A100-SXM4-40GB, 00000000:3B:00.0, 545.23.08, P0, 4, 4, 44, 3 %, 0 %, 40960 MiB, 1534 MiB, 38803 MiB
2024/06/01 11:27:25.361, NVIDIA A100-SXM4-40GB, 00000000:8B:00.0, 545.23.08, P0, 4, 4, 43, 3 %, 0 %, 40960 MiB, 666 MiB, 39671 MiB
2024/06/01 11:27:25.376, NVIDIA A100-SXM4-40GB, 00000000:3B:00.0, 545.23.08, P0, 4, 4, 44, 1 %, 0 %, 40960 MiB, 910 MiB, 39427 MiB</pre>

	<script src="prism.js"></script>
	<script src="footer.js"></script>
</body>
</html>
