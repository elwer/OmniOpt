# DESCRIPTION: Hex-Scatter plot
# EXPECTED FILES: results.csv
# TEST_OUTPUT_MUST_CONTAIN: Number of evaluations shown
# TEST_OUTPUT_MUST_CONTAIN: mean result
# TEST_OUTPUT_MUST_CONTAIN: result

# TODO: Check if this script is able to react properly to --maximize'd runs

import argparse
import importlib.util
import os
import signal
import sys
import traceback

button = None

TEXTBOX_MINIMUM = None
TEXTBOX_MAXIMUM = None

MAXIMUM_TEXTBOX = None
MINIMUM_TEXTBOX = None

bins = None

script_dir = os.path.dirname(os.path.realpath(__file__))
helpers_file = f"{script_dir}/.helpers.py"
spec = importlib.util.spec_from_file_location(
    name="helpers",
    location=helpers_file,
)
helpers = importlib.util.module_from_spec(spec)
spec.loader.exec_module(helpers)

val_if_nothing_found = 99999999999999999999999999999999999999999999999999999999999
NO_RESULT = "{:.0e}".format(val_if_nothing_found)

args = None
fig = None

signal.signal(signal.SIGINT, signal.SIG_DFL)

try:
    import matplotlib
    import matplotlib.pyplot as plt
except ModuleNotFoundError as ee:
    print(f"Error: {ee}")
    sys.exit(244)

# Get shell variables or use default values
ORIGINAL_PWD = os.environ.get("ORIGINAL_PWD", "")

if ORIGINAL_PWD:
    os.chdir(ORIGINAL_PWD)

def set_title(df_filtered, result_column_values, num_entries, _min, _max):
    title = helpers.get_title(args, result_column_values, df_filtered, num_entries, _min, _max)

    fig.suptitle(title)

def check_args():
    global args

    if args.min and args.max:
        if args.min > args.max:
            args.max, args.min = args.min, args.max
        elif args.min == args.max:
            print("Max and min value are the same. May result in empty data")

    helpers.check_path(args.run_dir)

def plot_multiple_graphs(_params):
    non_empty_graphs, num_cols, axs, df_filtered, cmap, norm, parameter_combinations, num_rows, result_column_values = _params
    global bins

    scatter = None

    for i, (param1, param2) in enumerate(non_empty_graphs):
        row = i // num_cols
        col = i % num_cols
        if (len(args.exclude_params) and param1 not in args.exclude_params[0] and param2 not in args.exclude_params[0]) or len(args.exclude_params) == 0:
            try:
                _x = df_filtered[param1]
                _y = df_filtered[param2]

                if bins:
                    scatter = axs[row][col].hexbin(_x, _y, result_column_values, gridsize=args.gridsize, cmap=cmap, bins=bins)
                else:
                    scatter = axs[row][col].hexbin(_x, _y, result_column_values, norm=norm, gridsize=args.gridsize, cmap=cmap)
                axs[row][col].set_xlabel(param1)
                axs[row][col].set_ylabel(param2)
            except Exception as e:
                if "'Axes' object is not subscriptable" in str(e):
                    if bins:
                        scatter = axs.hexbin(_x, _y, result_column_values, gridsize=args.gridsize, cmap=cmap, bins=bins)
                    else:
                        scatter = axs.hexbin(_x, _y, result_column_values, norm=norm, gridsize=args.gridsize, cmap=cmap)
                    axs.set_xlabel(param1)
                    axs.set_ylabel(param2)
                elif "could not convert string to float" in str(e):
                    print("ERROR: " + str(e))

                    tb = traceback.format_exc()
                    print(tb)

                    sys.exit(177)
                else:
                    print("ERROR: " + str(e))

                    tb = traceback.format_exc()
                    print(tb)

                    sys.exit(17)

    axs = helpers.hide_empty_plots(parameter_combinations, num_rows, num_cols, axs)

    helpers.show_legend(args, fig, scatter, axs)

def plot_single_graph(_params):
    axs, df_filtered, cmap, norm, non_empty_graphs, result_column_values = _params
    _data = df_filtered

    _data = _data[:].values

    _x = []
    _y = []

    for _l in _data:
        _x.append(_l[0])
        _y.append(_l[1])

    global bins
    if bins:
        scatter = axs.hexbin(_x, _y, result_column_values, cmap=cmap, gridsize=args.gridsize, bins=bins)
    else:
        scatter = axs.hexbin(_x, _y, result_column_values, cmap=cmap, gridsize=args.gridsize, norm=norm)
    axs.set_xlabel(non_empty_graphs[0][0])
    axs.set_ylabel("result")

    return scatter

def plot_graphs(_params):
    global fig
    df, fig, axs, df_filtered, non_empty_graphs, num_subplots, parameter_combinations, num_rows, num_cols, result_column_values = _params

    cmap, norm, colors = helpers.get_color_list(df, args, plt)

    if colors is not None:
        pass # for fooling linter

    if num_subplots == 1 and len(non_empty_graphs[0]) == 1:
        plot_single_graph([axs, df_filtered, cmap, norm, non_empty_graphs, result_column_values])
    else:
        plot_multiple_graphs([non_empty_graphs, num_cols, axs, df_filtered, cmap, norm, parameter_combinations, num_rows, result_column_values])

    axs = helpers.hide_empty_plots(parameter_combinations, num_rows, num_cols, axs)

def get_args():
    global args
    parser = argparse.ArgumentParser(description='Plot optimization runs.', prog="plot")

    parser.add_argument('--run_dir', type=str, help='Path to a CSV file', required=True)
    parser.add_argument('--save_to_file', type=str, help='Save the plot to the specified file', default=None)
    parser.add_argument('--max', type=float, help='Maximum value', default=None)
    parser.add_argument('--min', type=float, help='Minimum value', default=None)
    parser.add_argument('--darkmode', help='Enable darktheme', action='store_true', default=False)
    parser.add_argument('--merge_with_previous_runs', action='append', nargs='+', help="Run-Dirs to be merged with", default=[])
    parser.add_argument('--exclude_params', action='append', nargs='+', help="Params to be ignored", default=[])

    parser.add_argument('--allow_axes', action='append', nargs='+', help="Allow specific axes only (parameter names)", default=[])

    parser.add_argument('--no_legend', help='Disables legend', action='store_true', default=False)
    parser.add_argument('--bins', type=str, help='Number of bins for distribution of results', default=None)

    parser.add_argument('--gridsize', type=int, help='Gridsize for hex plots', default=5)
    parser.add_argument('--no_plt_show', help='Disable showing the plot', action='store_true', default=False)

    args = parser.parse_args()

    global bins

    if args.bins:
        if not (args.bins == "log" or helpers.looks_like_int(args.bins)):
            print(f"Error: --bin must be 'log' or a number, or left out entirely. Is: {args.bins}")
            sys.exit(193)

        if helpers.looks_like_int(args.bins):
            bins = int(args.bins)
        else:
            bins = args.bins

    check_args()

    return args

def main():
    global args

    matplotlib = helpers.use_matplotlib(args)

    csv_file_path = helpers.get_csv_file_path(args)

    df = helpers.get_data(NO_RESULT, csv_file_path, args.min, args.max, None, True)

    old_headers_string = ','.join(sorted(df.columns))

    df = helpers.merge_df_with_old_data(args, df, NO_RESULT, args.min, args.max, old_headers_string)

    nr_of_items_before_filtering = len(df)
    df_filtered = helpers.get_df_filtered(args, df)

    helpers.check_min_and_max(len(df_filtered), nr_of_items_before_filtering, csv_file_path, args.min, args.max)

    parameter_combinations = helpers.get_parameter_combinations(df_filtered)

    non_empty_graphs = helpers.get_non_empty_graphs(parameter_combinations, df_filtered, True)

    num_subplots, num_cols, num_rows = helpers.get_num_subplots_rows_and_cols(non_empty_graphs)

    global fig
    fig, axs = plt.subplots(num_rows, num_cols, figsize=(15 * num_cols, 7 * num_rows))

    result_column_values = helpers.get_result_column_values(df, csv_file_path)

    plot_graphs([df, fig, axs, df_filtered, non_empty_graphs, num_subplots, parameter_combinations, num_rows, num_cols, result_column_values])

    if not args.no_legend:
        set_title(df_filtered, result_column_values, len(df_filtered), args.min, args.max)

        fig = helpers.set_margins(fig)

        fig.canvas.manager.set_window_title("Hex-Scatter: " + str(args.run_dir))

    if args.save_to_file:
        helpers.save_to_file(fig, args, plt)
    else:
        global button, MAXIMUM_TEXTBOX, MINIMUM_TEXTBOX, TEXTBOX_MINIMUM, TEXTBOX_MAXIMUM

        button, MAXIMUM_TEXTBOX, MINIMUM_TEXTBOX, TEXTBOX_MINIMUM, TEXTBOX_MAXIMUM = helpers.create_widgets([plt, button, MAXIMUM_TEXTBOX, MINIMUM_TEXTBOX, args, TEXTBOX_MINIMUM, TEXTBOX_MAXIMUM, update_graph])

        if not args.no_plt_show:
            plt.show()

        update_graph(args.min, args.max)

# Define update function for the button
def update_graph(event=None, _min=None, _max=None):
    global fig, ax, button, MAXIMUM_TEXTBOX, MINIMUM_TEXTBOX, args

    if event: # only for fooling pylint...
        pass

    filter_out_strings = True
    helpers._update_graph([plt, fig, MINIMUM_TEXTBOX, MAXIMUM_TEXTBOX, _min, _max, args, NO_RESULT, filter_out_strings, set_title, plot_graphs, button])

if __name__ == "__main__":
    try:
        get_args()

        theme = "fast"

        if args.darkmode:
            theme = "dark_background"

        with plt.style.context(theme):
            main()
    except KeyboardInterrupt:
        sys.exit(0)
