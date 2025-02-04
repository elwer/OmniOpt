<h1>Create <samp>run.sh</samp>-file &amp; modify your program</h1>

<!-- This is needed to prepare your program for OmniOpt2 -->

<div id="toc"></div>

<h2 id="overview_what_needs_to_be_done">Overview: what needs to be done</h2>

<p>There are basically three steps you need to do to optimize your program with OmniOpt2:</p>

<ul>
	<li>Your program needs to be able to run on Linux, and especially on the HPC System, i.e. you need to use default dependencies or install the dependencies of your program into a virtual environment (or similar)</li>
	<li>Your program needs to accept it's hyperparameters via the command like, so you can call it like this: <span class="invert_in_dark_mode"><code class="language-python"><samp>python3 my_experiment.py --epochs=10 --learning_rate=0.05</samp></code></span> (or similar)</li>
	<li>Your program needs to print it's result (i.e. e.g. it's loss) in a standardized form. This can be achieved in python by doing: <span class="invert_in_dark_mode"><code class="language-python"><samp>print(f"RESULT: {loss}")</samp></code></span></li>
</ul>

<h2 id="script-example">Script Example</h2>
<p>To make your script robust enough for the environment of OmniOpt2 on HPC-Systems,
it is recommended that you do not run your script directly in the objective program
string. Rather, it is recommended that you create a <samp>run.sh</samp>-file from which
your program gets run.</p>

<p>It may look like this:</p>

<pre class="invert_in_dark_mode"><code class="language-bash">#!/bin/bash -l
# ^ Shebang-Line, so that it is known that this is a bash file
# -l means 'load this as login shell', so that /etc/profile gets loaded and you can use 'module load' or 'ml' as usual

# If you use this script not via `./run.sh' or just `srun run.sh', but like `srun bash run.sh', please add the '-l' there too.
# Like this:
# srun bash -l run.sh

# Load modules your program needs, always specify versions!
ml TensorFlow/2.3.1-fosscuda-2019b-Python-3.7.4 # Or whatever modules you need

# Load specific virtual environment (if applicable)
source /path/to/environment/bin/activate

# Load your script. $@ is all the parameters that are given to this run.sh file.
python3 /absolute/path/to_script.py $@

exit $? # Exit with exit code of python
</code></pre>

<p>Even though <samp>sbatch</samp> may inherit shell variables like loaded modules,
it is not recommended to rely on that heavily, because, especially when
copying the <samp>curl</samp>-command from this website, you may forget loading
the correct modules. This makes your script much more robust to changes.</p>

<p>Also, always load specific module-versions and never let <samp>lmod</samp> guess
the versions you want. Once these change, you'll almost certainly have problems
otherwise.</p>

<h2 id="argument-parsing">Parse Arguments from the Command Line</h2>

<h3 id="sys-argv">Using sys.argv</h3>
<p>The following Python program demonstrates how to parse command line arguments using <samp>sys.argv</samp>:</p>

<pre class="invert_in_dark_mode"><code class="language-python">import sys
epochs = int(sys.argv[1])
learning_rate = float(sys.argv[2])
model_name = sys.argv[3]

if epochs &lt;= 0:
	print("Error: Number of epochs must be positive")
	sys.exit(1)
if not 0 &lt; learning_rate &lt; 1:
	print("Error: Learning rate must be between 0 and 1")
	sys.exit(2)
print(f"Running with epochs={epochs}, learning_rate={learning_rate}, model_name={model_name}")

# Your code here

# loss = model.fit(...)

loss = epochs + learning_rate

print(f"RESULT: {loss}")
</code></pre>

<p>Example call:</p>
<pre class="invert_in_dark_mode"><code class="language-bash">python3 script.py 10 0.01 MyModel</code></pre>
<p>Example OmniOpt2-call:</p>
<pre class="invert_in_dark_mode"><code class="language-bash">python3 script.py %(epochs) %(learning_rate) %(model_name)</code></pre>

<h3 id="argparse">Using argparse</h3>
<p>The following Python program demonstrates how to parse command line arguments using <samp>argparse</samp>:</p>

<pre class="invert_in_dark_mode"><code class="language-python">import argparse
import sys

parser = argparse.ArgumentParser(description="Run a training script with specified parameters.")
parser.add_argument("epochs", type=int, help="Number of epochs")
parser.add_argument("learning_rate", type=float, help="Learning rate")
parser.add_argument("model_name", type=str, help="Name of the model")

args = parser.parse_args()

if args.epochs &lt;= 0:
	print("Error: Number of epochs must be positive")
	sys.exit(1)
if not 0 &lt; args.learning_rate &lt; 1:
	print("Error: Learning rate must be between 0 and 1")
	sys.exit(2)

print(f"Running with epochs={args.epochs}, learning_rate={args.learning_rate}, model_name={args.model_name}")

# Your code here

# loss = model.fit(...)

loss = args.epochs + args.learning_rate

print(f"RESULT: {loss}")
</code></pre>

<p>Example call:</p>
<pre class="invert_in_dark_mode"><code class="language-bash">python3 script.py --epochs 10 --learning_rate 0.01 --model_name MyModel</code></pre>
<p>Example OmniOpt2-call:</p>
<pre class="invert_in_dark_mode"><code class="language-bash">python3 script.py --epochs %(epochs) --learning_rate %(learning_rate) --model_name %(model_name)</code></pre>

<h4>Advantages of using <samp>argparse</samp></h4>
<ul>
	<li>Order of arguments does not matter; they are matched by name.</li>
	<li>Type checking is automatically handled based on the type specified in <samp>add_argument</samp>.</li>
	<li>Generates helpful usage messages if the arguments are incorrect or missing.</li>
	<li>Supports optional arguments and more complex argument parsing needs.</li>
</ul>
