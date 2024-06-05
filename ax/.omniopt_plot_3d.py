import sys
from pprint import pprint
def dier (msg):
    pprint(msg)
    sys.exit(1)
import argparse
import pandas as pd
import itertools
import pyvista as pv

def main():
    parser = argparse.ArgumentParser(description='3D Scatter Plot from CSV')
    parser.add_argument('--run_dir', type=str, required=True, help='Directory containing the CSV file')
    args = parser.parse_args()

    csv_file_path = f"{args.run_dir}/pd.csv"
    try:
        dataframe = pd.read_csv(csv_file_path)

        # Columns to ignore
        ignore_columns = ['trial_index', 'arm_name', 'trial_status', 'generation_method', 'result']
        dynamic_columns = [col for col in dataframe.columns if col not in ignore_columns]

        # Generate all permutations of 3 columns
        column_permutations = list(itertools.combinations(dynamic_columns, 3))

        # Create a plotter with the appropriate shape
        num_plots = len(column_permutations)
        plotter_shape = (num_plots // 2 + num_plots % 2, 2)  # Create a shape that fits all plots
        try:
            plotter = pv.Plotter(shape=plotter_shape)
        except ValueError as e:
            print(f"Error: {e} This may happen when your pd.csv has no result column or you don't have at least 3 numeric columns.")
            sys.exit(12)

        plotted = 0
        for index, (col1, col2, col3) in enumerate(column_permutations):
            row, col = divmod(index, 2)
            plotter.subplot(row, col)

            points = dataframe[[col1, col2, col3]].values
            scalars = dataframe['result']

            labels = dict(xlabel=col1, ylabel=col2, zlabel=col3)

            try:
                plotter.add_mesh(pv.PolyData(points),
                                 scalars=scalars,
                                 point_size=10,
                                 render_points_as_spheres=True,
                                 cmap="coolwarm",  # Colormap ranging from blue to red
                                 scalar_bar_args={'title': 'Result'})

                plotter.show_grid()
                plotter.add_axes(interactive=True, **labels)
                plotter.add_scalar_bar(title='Result')
            except TypeError as e:
                print(f"Cannot plot {col1}, {col2}, {col3}")
                plotted += 1

        if plotted:
            plotter.show()
        else:
            print(f"Did not plot anything")
            sys.exit(42)
    except FileNotFoundError:
        print(f"pd.csv cannot be found under {args.run_dir}")
        sys.exit(45)

if __name__ == "__main__":
    main()

